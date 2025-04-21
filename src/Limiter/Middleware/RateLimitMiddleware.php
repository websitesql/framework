<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Limiter\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WebsiteSQL\Framework\Core\App;
use WebsiteSQL\Framework\Exceptions\RateLimitExceededException;
use WebsiteSQL\Framework\Limiter\Limiter;

class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * @var Limiter Limiter controller
     */
    private Limiter $limiter;
    
    /**
     * @var string|null Rate limit profile to use
     */
    private ?string $profile;
    
    /**
     * @var bool Whether to include rate limit headers in responses
     */
    private bool $headers;
    
    /**
     * Constructor
     *
     * @param App $app
     * @param string|null $profile Rate limit profile to use
     * @param bool $headers Whether to include rate limit headers in responses
     */
    public function __construct(App $app, ?string $profile = null, bool $headers = true)
    {
        $this->limiter = new Limiter($app);
        $this->profile = $profile;
        $this->headers = $headers;
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
        
        // Check rate limit using the Limiter controller
        $rateData = $this->limiter->check($ipAddress, $userId, $endpoint, $this->profile);
        
        // If rate limit is exceeded
        if (!$rateData['allowed']) {
            throw new RateLimitExceededException();
        }
        
        // Process the request
        $response = $handler->handle($request);
        
        // Add rate limit headers if enabled
        if ($this->headers) {
            $response = $this->addRateLimitHeaders($response, $rateData);
        }
        
        return $response;
    }
    
    /**
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
     * Add rate limit headers to response
     *
     * @param ResponseInterface $response
     * @param array $rateData
     * @return ResponseInterface
     */
    protected function addRateLimitHeaders(ResponseInterface $response, array $rateData): ResponseInterface
    {
        return $response
            ->withHeader('X-RateLimit-Limit', (string)$rateData['limit'])
            ->withHeader('X-RateLimit-Remaining', (string)$rateData['remaining'])
            ->withHeader('X-RateLimit-Reset', (string)$rateData['reset']);
    }
}