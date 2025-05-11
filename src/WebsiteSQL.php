<?php declare(strict_types=1);

namespace WebsiteSQL;

class WebsiteSQL {
	/**
	 * This object is used to store the instances of the classes that are created by the framework
	 *
	 * @var array<string, object>
	 * @access private
	 */
    private static $instances = [];
    
    /**
     * Custom registered instances or classes
     *
     * @var array<string, object|string>
     * @access private
     */
    private static $registry = [];
    
    /**
     * Get the Config instance
     * 
     * @return \WebsiteSQL\Config\Config
     */
    public static function config() {
        return self::getInstance('\WebsiteSQL\Config\Config');
    }
    
    /**
     * Get the Database instance
     * 
     * @return \WebsiteSQL\Database\DB
     */
    public static function db() {
        return self::getInstance('\WebsiteSQL\Database\DB');
    }

    /**
	 * Return a new Utilities instance
	 * 
	 * @return \WebsiteSQL\Utilities\Utilities
	 */
	public static function utils() {
		return new \WebsiteSQL\Utilities\Utilities();
	}
    
    /**
     * Get the Router instance
     * 
     * @return \WebsiteSQL\Router\Router
     */
    public static function router() {
        return self::getInstance('\WebsiteSQL\Router\Router');
    }
    
    /**
     * Get the Middleware manager instance
     * 
     * @return \WebsiteSQL\Middleware\MiddlewareManager
     */
    public static function middleware() {
        return self::getInstance('\WebsiteSQL\Middleware\MiddlewareManager');
    }
    
    /**
     * Get the Cron manager instance
     * 
     * @return \WebsiteSQL\Cron\CronManager
     */
    public static function cron() {
        return self::getInstance('\WebsiteSQL\Cron\CronManager');
    }
    
    /**
     * Create a new Request instance from global variables
     * 
     * @return \WebsiteSQL\Router\Request
     */
    public static function request() {
        return \WebsiteSQL\Router\Request::createFromGlobals();
    }
    
    /**
     * Create a new Response instance
     * 
     * @return \WebsiteSQL\Router\Response
     */
    public static function response() {
        return new \WebsiteSQL\Router\Response();
    }
    
    /**
     * Start the application
     * 
     * @return void
     */
    public static function start() {
        // Initialize the router and dispatch the request
        self::router()->dispatch();
    }
    
    /**
     * Register a custom instance or class
     * 
     * @param string $name The name to register the instance or class under
     * @param object|string $instance The instance or class name to register
     * @return void
     */
    public static function register($name, $instance) {
        self::$registry[$name] = $instance;
    }
    
    /**
     * Handle calls to undefined static methods
     * 
     * @param string $name Method name
     * @param array $arguments Method arguments
     * @return mixed
     * @throws \Exception If the method doesn't exist
     */
    public static function __callStatic($name, $arguments) {
        if (isset(self::$registry[$name])) {
            $instance = self::$registry[$name];
            
            // If it's a class name, instantiate it
            if (is_string($instance) && class_exists($instance)) {
                self::$registry[$name] = new $instance(...$arguments);
                return self::$registry[$name];
            }
            
            // If it's a callable, call it with the arguments
            if (is_callable($instance)) {
                return call_user_func_array($instance, $arguments);
            }
            
            // Otherwise, return the instance
            return $instance;
        }
        
        throw new \Exception("Method {$name} does not exist");
    }
    
    /**
     * Get a singleton instance of a class
     * 
     * @param string $class Fully qualified class name
     * @return object Instance of the class
     */
    private static function getInstance($class) {
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class();
        }
        return self::$instances[$class];
    }
}