<?php declare(strict_types=1);

namespace WebsiteSQL\Http;

class MiddlewareManager {
	/**
	 * This object is used to store the middleware registered in the framework
	 * 
	 * @var array<string, callable|string>
	 * @access private
	 */
    private $middleware = [];
    
	/**
	 * Register a middleware with a name and a callback
	 * 
	 * @param string $name The name of the middleware
	 * @param callable|string $callback The callback or class@method to be executed
	 * @return $this
	 * @throws \Exception If the middleware name is already registered
	 */
    public function register($name, $callback) {
        $this->middleware[$name] = $callback;
        return $this;
    }
    
	/**
	 * Run the middleware by name
	 * 
	 * @param string $name The name of the middleware to run
	 * @return mixed The result of the middleware execution
	 * @throws \Exception If the middleware is not registered
	 */
    public function run($name) {
        if (!isset($this->middleware[$name])) {
            throw new \Exception("Middleware {$name} not registered");
        }
        
        $middleware = $this->middleware[$name];
        
        if (is_callable($middleware)) {
            return call_user_func($middleware);
        } elseif (is_string($middleware)) {
            list($class, $method) = explode('@', $middleware);
            $instance = new $class();
            return $instance->$method();
        }
        
        return true;
    }
}