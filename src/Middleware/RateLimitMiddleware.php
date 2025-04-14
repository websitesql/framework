<?php

namespace WebsiteSQL\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WebsiteSQL\Framework\App;
use WebsiteSQL\Framework\Exceptions\RateLimitExceededException;
use Laminas\Diactoros\Response\JsonResponse;

class RateLimitMiddleware implements MiddlewareInterface
{
    /*
     * This object holds the App container instance
     * 
     * @var App
     */
    private App $app;

    /*
     * This object holds the default options
     * 
     * @var array
     */
    protected array $options = [
        'unauthenticated' => [
            'limit' => 60,     // Number of requests
            'window' => 3600,  // Time window in seconds (1 hour)
        ],
        'authenticated' => [
            'limit' => 1000,   // Number of requests
            'window' => 3600,  // Time window in seconds (1 hour)
        ],
        'headers' => true      // Whether to include rate limit headers in responses
    ];

    /*
     * Constructor
     * 
     * @param App $app
	 * @param array $options
     */
    public function __construct(App $app, array $options = [])
    {
		$this->app = $app;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Process the middleware
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Determine if user is authenticated
        $isAuthenticated = $this->isAuthenticated($request);
        
        // Get user ID and IP address
        $userId = $isAuthenticated ? $this->getUserId($request) : null;
        $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
        
        // Get the API endpoint
        $endpoint = $this->getEndpoint($request);
        
        // Get the appropriate rate limit configuration
        $config = $isAuthenticated ? $this->options['authenticated'] : $this->options['unauthenticated'];
        
        // Check rate limit
        $rateData = $this->checkRateLimit($userId, $ipAddress, $endpoint, $config);

        // Process the request
        $response = $handler->handle($request);
        
        // Add rate limit headers if enabled
        if ($this->options['headers']) {
            $response = $this->addRateLimitHeaders($response, $rateData);
        }

        // If rate limit is exceeded
        if (!$rateData['allowed']) {
            // Throw exception instead of creating response directly
            throw new RateLimitExceededException();
        }
        
        return $response;
    }

    /*
     * Check if the request is authenticated
     * 
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isAuthenticated(ServerRequestInterface $request): bool
    {
        $user = $request->getAttribute('user');
        return !empty($user);
    }

    /**
     * Get the user ID from the request
     * 
     * @param ServerRequestInterface $request
     * @return int|null
     */
    protected function getUserId(ServerRequestInterface $request): ?int
    {
        $user = $request->getAttribute('user');
        return $user['id'] ?? null;
    }

    /**
     * Get the API endpoint from the request
     * 
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function getEndpoint(ServerRequestInterface $request): string
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        return $method . ' ' . $path;
    }

    /**
     * Check rate limit and update counters
     * 
     * @param int|null $userId
     * @param string $ipAddress
     * @param string $endpoint
     * @param array $config
     * @return array
     */
    protected function checkRateLimit(?int $userId, string $ipAddress, string $endpoint, array $config): array
    {
        $limit = $config['limit'] ?? 60;
        $window = $config['window'] ?? 3600;
        
        // Get current timestamp
        $now = time();
        
        // For rolling window, we look back from current time
        $windowStartTime = $now - $window;
        
        // Count existing requests within the rolling time window using Medoo
        $where = [
            "ip_address" => $ipAddress,
            "api_endpoint" => $endpoint,
            "created_at[>]" => date('Y-m-d H:i:s', $windowStartTime)
        ];
        
        if ($userId !== null) {
            $where = [
                "user" => $userId,
                "api_endpoint" => $endpoint,
                "created_at[>]" => date('Y-m-d H:i:s', $windowStartTime)
            ];
        }
        
        $count = $this->app->getDatabase()->count(
            $this->app->getStrings()->getTableRateLimits(), 
            $where
        );
        
        // Calculate remaining requests and whether request is allowed
        $remaining = $limit - $count;
        $allowed = $remaining > 0;
        
        // Record this request if allowed
        if ($allowed) {
            $this->app->getDatabase()->insert(
                $this->app->getStrings()->getTableRateLimits(), 
                [
                    "user" => $userId,
                    "ip_address" => $ipAddress,
                    "api_endpoint" => $endpoint,
                    "created_at" => date('Y-m-d H:i:s')
                ]
            );
            
            // Decrement remaining count since we've just used one
            $remaining--;
        }
        
        // With rolling window, reset time depends on when the oldest request will expire
        // Find the oldest request in the current window
        $resetQuery = $userId !== null 
            ? ["user" => $userId, "api_endpoint" => $endpoint, "ORDER" => ["created_at" => "ASC"], "LIMIT" => 1]
            : ["ip_address" => $ipAddress, "api_endpoint" => $endpoint, "ORDER" => ["created_at" => "ASC"], "LIMIT" => 1];
        
        $oldestRequest = $this->app->getDatabase()->get(
            $this->app->getStrings()->getTableRateLimits(), 
            "created_at",
            $resetQuery
        );
        
        // If we have at least one request, calculate when it will expire
        $resetTime = $now + $window; // Default if no requests yet
        if ($oldestRequest && $count >= $limit) {
            $oldestTime = strtotime($oldestRequest);
            $resetTime = $oldestTime + $window;
        }
        
        return [
            'limit' => $limit,
            'remaining' => max(0, $remaining),
            'reset' => $resetTime,
            'allowed' => $allowed,
        ];
    }

    /**
     * Create response for rate limit exceeded
     */
    protected function createRateLimitExceededResponse(array $rateData): ResponseInterface
    {
		$response = new JsonResponse([
			'error' => 'Rate limit exceeded',
			'status' => 429,
			'message' => 'Too many requests. Please try again later.',
		], 429);
		
        return $response->withHeader('X-RateLimit-Limit', $rateData['limit'])
			->withHeader('X-RateLimit-Remaining', $rateData['remaining'])
			->withHeader('X-RateLimit-Reset', $rateData['reset'])
			->withHeader('Retry-After', max(1, $rateData['reset'] - time()));
    }

    /**
     * Add rate limit headers to response
     */
    protected function addRateLimitHeaders(ResponseInterface $response, array $rateData): ResponseInterface
    {
        return $response
            ->withHeader('X-RateLimit-Limit', $rateData['limit'])
            ->withHeader('X-RateLimit-Remaining', $rateData['remaining'])
            ->withHeader('X-RateLimit-Reset', $rateData['reset']);
    }
}