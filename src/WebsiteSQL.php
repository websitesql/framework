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