<?php declare(strict_types=1);

namespace WebsiteSQL\Http;

use WebsiteSQL\Http\Message\ResponseInterface;
use WebsiteSQL\Http\Message\ServerRequestInterface;
use WebsiteSQL\Http\Middleware\MiddlewareInterface;
use WebsiteSQL\Http\Middleware\RequestHandlerInterface;

class MiddlewareManager {
	/**
	 * This object is used to store the middleware registered in the framework
	 * 
	 * @var array<string, callable|string|MiddlewareInterface>
	 * @access private
	 */
    private $middleware = [];
    
    /**
     * This array stores global middleware that runs on every request
     * 
     * @var array<string>
     * @access private
     */
    private $globalMiddleware = [];
    
	/**
	 * Register a middleware with a name and a callback
	 * 
	 * @param string $name The name of the middleware
	 * @param callable|string|MiddlewareInterface $middleware The middleware implementation
	 * @return $this
	 * @throws \Exception If the middleware name is already registered
	 */
    public function register($name, $middleware) {
        $this->middleware[$name] = $middleware;
        return $this;
    }
    
    /**
     * Add middleware to the global stack
     * 
     * @param string|array $middlewareNames The middleware name(s) to add to the global stack
     * @return $this
     */
    public function addGlobal($middlewareNames) {
        if (is_string($middlewareNames)) {
            $middlewareNames = [$middlewareNames];
        }
        
        $this->globalMiddleware = array_merge($this->globalMiddleware, $middlewareNames);
        return $this;
    }
    
    /**
     * Get registered global middleware
     * 
     * @return array The global middleware stack
     */
    public function getGlobalMiddleware() {
        return $this->globalMiddleware;
    }
    
	/**
	 * Run the middleware by name
	 * 
	 * @param string $name The name of the middleware to run
	 * @param ServerRequestInterface $request The request object
	 * @param ResponseInterface $response The response object
	 * @param callable $next The next middleware in the stack
	 * @return ResponseInterface The processed response
	 * @throws \Exception If the middleware is not registered
	 */
    public function run($name, ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        if (!isset($this->middleware[$name])) {
            throw new \Exception("Middleware {$name} not registered");
        }
        
        $middleware = $this->middleware[$name];
        
        if ($middleware instanceof MiddlewareInterface) {
            // PSR-15 middleware
            return $middleware->process($request, new RequestHandler($next));
        } elseif (is_callable($middleware)) {
            // Callable middleware
            return $middleware($request, $response, $next);
        } elseif (is_string($middleware)) {
            // Class@method style middleware
            list($class, $method) = explode('@', $middleware);
            $instance = new $class();
            return $instance->$method($request, $response, $next);
        }
        
        // If we get here, something went wrong
        return $next($request, $response);
    }
    
    /**
     * Execute a middleware stack for a route
     *
     * @param array $middlewareStack Array of middleware names to execute
     * @param ServerRequestInterface $request The request object
     * @param ResponseInterface $response The response object
     * @param callable $handler The final handler after all middleware
     * @return ResponseInterface The processed response
     */
    public function executeStack(array $middlewareStack, ServerRequestInterface $request, ResponseInterface $response, callable $handler) {
        // Combine global middleware with route middleware
        $fullStack = array_merge($this->globalMiddleware, $middlewareStack);
        
        $next = function (ServerRequestInterface $req, ResponseInterface $res) use ($handler) {
            return $handler($req, $res);
        };
        
        // Build the middleware stack in reverse
        for ($i = count($fullStack) - 1; $i >= 0; $i--) {
            $middleware = $fullStack[$i];
            $next = function (ServerRequestInterface $req, ResponseInterface $res) use ($middleware, $next) {
                return $this->run($middleware, $req, $res, $next);
            };
        }
        
        // Execute the middleware stack
        return $next($request, $response);
    }
}

/**
 * RequestHandler class to convert callable middleware to PSR-15 format
 */
class RequestHandler implements RequestHandlerInterface
{
    private $handler;
    
    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = $this->handler;
        return $handler($request);
    }
}