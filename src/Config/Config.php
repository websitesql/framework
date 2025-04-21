<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Config;

use Dotenv\Dotenv;
use Exception;

class Config
{
    /*
     * This string holds the application base path
     * 
     * @var string
     */
    private string $basePath;

    /*
     * This array holds the configuration values
     * 
     * @var array
     */
    private array $config = [];

    /*
     * Constructor
     * 
     * @param string $basePath
     */
    public function __construct(string $basePath)
    {
        // Set the base path
        $this->basePath = $basePath;

		// Check if the base path is valid
		if (!is_dir($this->basePath)) {
			throw new Exception('Base path is not a valid directory');
		}

        // Load environment variables
        $this->loadEnvironmentVariables();
        
        // Load the configuration files
        $this->config = $this->loadConfig();
    }

    /*
     * This method returns a configuration value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
	public function get(string $key, mixed $default = null): mixed
    {
        // Support dot notation (app.name)
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        
        return $value;
    }

    /*
     * This method loads the environment variables from .env file
     * 
     * @return void
     */
	private function loadEnvironmentVariables(): void
    {
        // Check if the .env file exists
        if (!file_exists($this->basePath . '/.env')) {
            throw new Exception('Environment variables file not found');
        }

        // Load environment variables
        $dotenv = Dotenv::createImmutable($this->basePath);
        $dotenv->load();
    }

    /*
     * This method loads the configuration from config files
     * 
     * @return array
     */
    private function loadConfig(): array
    {
        $configPath = $this->basePath . '/config';
        
        // Check if config directory exists
        if (!is_dir($configPath)) {
            throw new Exception('Configuration directory not found');
        }
        
        // Initialize config array
        $config = [];
        
        // Scan the config directory for PHP files
        $files = scandir($configPath);
        
        foreach ($files as $file) {
            $filePath = $configPath . '/' . $file;
            
            // Check if it's a PHP file
            if (is_file($filePath) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $key = pathinfo($file, PATHINFO_FILENAME);
                $fileConfig = require $filePath;
                
                if (is_array($fileConfig)) {
                    $config[$key] = $fileConfig;
                }
            }
        }
        
        return $config;
    }
}