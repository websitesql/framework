<?php declare(strict_types=1);

namespace WebsiteSQL\Middleware;

use WebsiteSQL\Router\Request;
use WebsiteSQL\Router\Response;

class MiddlewareManager {
    /**
     * Registered middleware
     *
     * @var array
     * @access protected
     */
    protected $middleware = [];
    
    /**
     * Register a middleware with a name
     *
     * @param string $name Middleware name
     * @param callable|string $handler Middleware handler
     * @return $this
     */
    public function register($name, $handler) {
        $this->middleware[$name] = $handler;
        return $this;
    }
    
    /**
     * Check if middleware exists
     *
     * @param string $name Middleware name
     * @return bool
     */
    public function has($name) {
        return isset($this->middleware[$name]);
    }
    
    /**
     * Get middleware handler by name
     *
     * @param string $name Middleware name
     * @return callable|string|null
     */
    public function get($name) {
        return $this->middleware[$name] ?? null;
    }
    
    /**
     * Process middleware and the route handler
     *
     * @param array $middleware Array of middleware names to process
     * @param callable $routeHandler The final route handler
     * @param Request $request Request instance
     * @param Response $response Response instance
     * @param array $routeArgs Route arguments
     * @return mixed
     */
    public function process(array $middleware, $routeHandler, Request $request, Response $response, array $routeArgs = []) {
        $currentIndex = 0;
        
        $next = function($request, $response) use (&$next, &$currentIndex, $middleware, $routeHandler, $routeArgs) {
            // If we've gone through all middleware, execute the route handler
            if ($currentIndex >= count($middleware)) {
                return call_user_func_array($routeHandler, array_merge([$request, $response], $routeArgs));
            }
            
            // Get the current middleware
            $middlewareName = $middleware[$currentIndex++];
            $middlewareHandler = $this->get($middlewareName);
            
            if (!$middlewareHandler) {
                throw new \Exception("Middleware not found: {$middlewareName}");
            }
            
            // Set up the request with a process function that moves to next middleware
            $request->setProcess(function() use ($next, $request, $response) {
                return $next($request, $response);
            });
            
            // Execute the middleware
            return $this->executeMiddleware($middlewareHandler, $request, $response);
        };
        
        return $next($request, $response);
    }
    
    /**
     * Execute middleware
     *
     * @param callable|string $middleware
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    protected function executeMiddleware($middleware, $request, $response) {
        if (is_callable($middleware)) {
            return call_user_func($middleware, $request, $response);
        } elseif (is_string($middleware)) {
            if (strpos($middleware, '@') !== false) {
                list($class, $method) = explode('@', $middleware);
                $instance = new $class();
                return call_user_func([$instance, $method], $request, $response);
            } else {
                // If it's a class name without a method, instantiate and call __invoke
                $instance = new $middleware();
                return $instance($request, $response);
            }
        }
        
        return null;
    }
}