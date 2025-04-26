<?php declare(strict_types=1);

namespace WebsiteSQL\Config;

class Config {
    private $config = [];
    private $envCache = null;
    
    public function add(array $config) {
        $this->config = array_merge($this->config, $config);
        return $this;
    }
    
    public function get($key, $default = null) {
        $keys = explode('.', $key);
        $config = $this->config;
        
        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                return $default;
            }
            $config = $config[$segment];
        }
        
        return $config;
    }
    
    public function set($key, $value) {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                $config[$segment] = $value;
            } else {
                if (!isset($config[$segment]) || !is_array($config[$segment])) {
                    $config[$segment] = [];
                }
                $config = &$config[$segment];
            }
        }
        
        return $this;
    }
    
    /**
     * Get a value from the .env file in the project root
     *
     * @param string $key The environment variable key
     * @param mixed $default The default value if key not found
     * @return mixed The value from .env or default if not found
     */
    public function env(string $key, $default = null)
    {
        // First check if the key is already in $_ENV or $_SERVER
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        
        // If the env vars haven't been cached yet, load them
        if ($this->envCache === null) {
            $this->loadEnvFile();
        }
        
        // Return the value from the cache or the default
        return $this->envCache[$key] ?? $default;
    }
    
    /**
     * Load and parse the .env file
     *
     * @return void
     */
    private function loadEnvFile(): void
    {
        $this->envCache = [];
        
        // Find the .env file path
        $envPath = $this->findEnvFile();
        
        if ($envPath && file_exists($envPath)) {
            // Parse the .env file
            $this->envCache = $this->parseEnvFile($envPath);
        }
    }
    
    /**
     * Find the .env file in the project root
     *
     * @return string|null The path to the .env file or null if not found
     */
    private function findEnvFile(): ?string
    {
        // Start from the current directory and go up until we find a .env file
        $dir = __DIR__;
        while ($dir !== '/' && $dir !== '\\' && dirname($dir) !== $dir) {
            $envPath = $dir . DIRECTORY_SEPARATOR . '.env';
            if (file_exists($envPath)) {
                return $envPath;
            }
            $dir = dirname($dir);
        }
        
        // Try document root as fallback
        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $envPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . '.env';
            if (file_exists($envPath)) {
                return $envPath;
            }
        }
        
        return null;
    }
    
    /**
     * Parse a .env file into an associative array
     *
     * @param string $path The path to the .env file
     * @return array The parsed environment variables
     */
    private function parseEnvFile(string $path): array
    {
        $vars = [];
        
        // Read the file line by line
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if ($lines === false) {
            return $vars;
        }
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse the key-value pair
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                
                // Remove quotes if they exist
                if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                } elseif (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                }
                
                $vars[$key] = $value;
                
                // Also set the variable in $_ENV and $_SERVER for future access
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
        
        return $vars;
    }
}