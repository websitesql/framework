<?php declare(strict_types=1);

namespace WebsiteSQL;

use WebsiteSQL\Http\Message\ResponseInterface;

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
     * Get the Router instance
     * 
     * @return \WebsiteSQL\Http\Router
     */
    public static function router() {
        return self::getInstance('\WebsiteSQL\Http\Router');
    }
	
    /**
     * Get the HTTP utilities instance
     * 
     * @return \WebsiteSQL\Http\Http
     */
	public static function http() {
		return self::getInstance('\WebsiteSQL\Http\Http');
	}
    
    /**
     * Get or register middleware
     * 
     * @param string|null $name Middleware name
     * @param callable|null $callback Middleware callback
     * @return \WebsiteSQL\Http\MiddlewareManager|\WebsiteSQL\Http\MiddlewareManager
     */
    public static function middleware($name = null, $callback = null) {
        $middlewareManager = self::getInstance('\WebsiteSQL\Http\MiddlewareManager');
        if ($name !== null && $callback !== null) {
            return $middlewareManager->register($name, $callback);
        }
        return $middlewareManager;
    }
    
    /**
     * Add a route to the router
     * 
     * @param string $method HTTP method
     * @param string $path Route path
     * @param callable|string $callback Route handler
     * @return \WebsiteSQL\Http\Route
     */
    public static function route($method, $path, $callback) {
        return self::router()->add($method, $path, $callback);
    }
    
    /**
     * Start the application by dispatching the router
     * 
     * @return ResponseInterface
     */
    public static function start() {
        return self::router()->dispatch();
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