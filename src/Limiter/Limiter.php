<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Limiter;

use WebsiteSQL\Framework\Core\App;

class Limiter
{
    /**
     * @var Kernel Kernel instance
     */
    protected App $app;
    
    /**
     * @var array Limiter configuration
     */
    protected array $config;
    
    /**
     * @var string Storage base path
     */
    protected string $storagePath;
    
    /**
     * Constructor
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->config = $this->app->getConfig()->get('limiter');
        $this->storagePath = $this->app->getBasePath() . '/storage/limiter';
        
        // Ensure storage directory exists
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }
    
    /**
     * Check rate limit for a request
     *
     * @param string $ipAddress IP address of the request
     * @param int|null $userId User ID if authenticated
     * @param string $endpoint The API endpoint being accessed
     * @param string $profile The limiter profile to use
     * @return array Rate limit data
     */
    public function check(string $ipAddress, ?int $userId, string $endpoint, string $profile = null): array
    {
        $profile = $profile ?? $this->config['default'] ?? 'default';
        
        // Get the profile configuration
        $profileConfig = $this->config['profiles'][$profile] ?? $this->config['profiles']['default'];
        
        // Check IP-based rate limit
        $ipLimitData = $this->checkIpLimit($ipAddress, $endpoint, $profileConfig['ip_address']);
        
        // If user is authenticated, also check user-based rate limit
        if ($userId !== null) {
            $userLimitData = $this->checkUserLimit($userId, $endpoint, $profileConfig['user']);
            
            // Use the most restrictive limit (lowest remaining)
            if ($userLimitData['remaining'] < $ipLimitData['remaining']) {
                return $userLimitData;
            }
        }
        
        return $ipLimitData;
    }
    
    /**
     * Check IP-based rate limit
     *
     * @param string $ipAddress
     * @param string $endpoint
     * @param array $config
     * @return array
     */
    protected function checkIpLimit(string $ipAddress, string $endpoint, array $config): array
    {
        $limit = $config['limit'] ?? 100;
        $window = $config['window'] ?? 60;
        
        $key = 'ip_' . md5($ipAddress . '_' . $endpoint);
        return $this->processLimit($key, $limit, $window);
    }
    
    /**
     * Check user-based rate limit
     *
     * @param int $userId
     * @param string $endpoint
     * @param array $config
     * @return array
     */
    protected function checkUserLimit(int $userId, string $endpoint, array $config): array
    {
        $limit = $config['limit'] ?? 1000;
        $window = $config['window'] ?? 60;
        
        $key = 'user_' . md5((string)$userId . '_' . $endpoint);
        return $this->processLimit($key, $limit, $window);
    }
    
    /**
     * Process rate limit check for a key
     *
     * @param string $key Unique identifier for this limit
     * @param int $limit Maximum requests allowed
     * @param int $window Time window in seconds
     * @return array Rate limit data
     */
    protected function processLimit(string $key, int $limit, int $window): array
    {
        $filename = $this->storagePath . '/' . $key . '.json';
        $now = time();
        $windowStartTime = $now - $window;
        
        // Load existing data
        $data = $this->loadData($filename);
        
        // Remove expired entries
        $data = array_filter($data, function($timestamp) use ($windowStartTime) {
            return $timestamp >= $windowStartTime;
        });
        
        // Count requests within the window
        $count = count($data);
        
        // Calculate remaining requests and if request is allowed
        $remaining = $limit - $count;
        $allowed = $remaining > 0;
        
        // Record this request if allowed
        if ($allowed) {
            $data[] = $now;
            $remaining--;
        }
        
        // Save the updated data
        $this->saveData($filename, $data);
        
        // Calculate reset time (when the oldest request expires)
        $resetTime = $now + $window;
        if (!empty($data) && $count >= $limit) {
            $resetTime = min($data) + $window;
        }
        
        return [
            'limit' => $limit,
            'remaining' => max(0, $remaining),
            'reset' => $resetTime,
            'allowed' => $allowed,
        ];
    }
    
    /**
     * Load rate limit data from file
     *
     * @param string $filename
     * @return array
     */
    protected function loadData(string $filename): array
    {
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            if ($content) {
                return json_decode($content, true) ?? [];
            }
        }
        return [];
    }
    
    /**
     * Save rate limit data to file
     *
     * @param string $filename
     * @param array $data
     * @return void
     */
    protected function saveData(string $filename, array $data): void
    {
        file_put_contents($filename, json_encode($data), LOCK_EX);
    }
}
