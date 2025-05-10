<?php declare(strict_types=1);

namespace WebsiteSQL;

use WebsiteSQL\Http\Router;
use WebsiteSQL\Http\Request;
use WebsiteSQL\Http\Response;

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
     * @return \WebsiteSQL\Http\Router
     */
    public static function router() {
        return self::getInstance('\WebsiteSQL\Http\Router');
    }
    
    /**
     * Create a new Request instance from global variables
     * 
     * @return \WebsiteSQL\Http\Request
     */
    public static function request() {
        return Request::createFromGlobals();
    }
    
    /**
     * Create a new Response instance
     * 
     * @return \WebsiteSQL\Http\Response
     */
    public static function response() {
        return new Response();
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