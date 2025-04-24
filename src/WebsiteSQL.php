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
    
    public static function config() {
        return self::getInstance('\WebsiteSQL\Config\Config');
    }
    
    public static function db() {
        return self::getInstance('\WebsiteSQL\Database\DB');
    }
    
    public static function router() {
        return self::getInstance('\WebsiteSQL\Http\Router');
    }
    
    public static function middleware($name = null, $callback = null) {
        $middlewareManager = self::getInstance('\WebsiteSQL\Http\MiddlewareManager');
        if ($name !== null && $callback !== null) {
            return $middlewareManager->register($name, $callback);
        }
        return $middlewareManager;
    }
    
    public static function route($method, $path, $callback) {
        return self::router()->add($method, $path, $callback);
    }
    
    public static function start() {
        return self::router()->dispatch();
    }
    
    private static function getInstance($class) {
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class();
        }
        return self::$instances[$class];
    }
}
