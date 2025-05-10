<?php declare(strict_types=1);

namespace WebsiteSQL\Http;

use WebsiteSQL\Http\Message\ResponseInterface;
use WebsiteSQL\Http\Message\ServerRequestInterface;
use WebsiteSQL\WebsiteSQL;

class Route {
    private $method;
    private $path;
    private $callback;
    private $middleware = [];
    private $name;
    
    public function __construct($method, $path, $callback) {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->callback = $callback;
    }
    
    public function middleware(array $middleware) {
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }
    
    public function name($name) {
        $this->name = $name;
        WebsiteSQL::router()->addNamed($name, $this);
        return $this;
    }
    
    public function matches($method, $uri) {
        if ($this->method !== strtoupper($method)) {
            return false;
        }
        
        $pattern = $this->pathToPattern($this->path);
        return preg_match($pattern, $uri);
    }
    
    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        // Extract parameters from the URI and add them to the request attributes
        $params = $this->extractParams($request->getRequestTarget());
        foreach ($params as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }
        
        // If no middleware, just execute the handler
        if (empty($this->middleware)) {
            return $this->executeHandler($request);
        }
        
        // Execute middleware stack
        return WebsiteSQL::middleware()->executeStack(
            $this->middleware,
            $request,
			$response,
            function (ServerRequestInterface $req) {
				return $this->executeHandler($req);
            }
        );
    }
    
    private function executeHandler(ServerRequestInterface $request): ResponseInterface {
        if (is_callable($this->callback)) {
            return call_user_func($this->callback, $request);
        } elseif (is_string($this->callback)) {
            list($controller, $method) = explode('@', $this->callback);
            $controllerInstance = new $controller();
            return $controllerInstance->$method($request);
        }
        
        // If callback is invalid, create a default response
        return WebsiteSQL::http()->jsonResponse(
			[
				'error' => [
					'message' => 'Invalid route callback.',
					'type' => 'INVALID_CALLBACK',
					'code' => 500
				]
			],
			500
		);
    }
    
    public function generateUrl($params = []) {
        $url = $this->path;
        
        foreach ($params as $key => $value) {
            $url = str_replace("{{$key}}", $value, $url);
        }
        
        return $url;
    }
    
    private function pathToPattern($path) {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return "#^{$pattern}$#";
    }
    
    private function extractParams($uri) {
        $pattern = $this->pathToPattern($this->path);
        preg_match($pattern, $uri, $matches);
        
        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }
        
        return $params;
    }
}

class RouteDecorator {
    /**
     * @var Router
     */
    protected $router;
    
    /**
     * @var int
     */
    protected $routeIndex;
    
    /**
     * Create a new RouteDecorator instance
     *
     * @param Router $router
     * @param int $routeIndex
     */
    public function __construct(Router $router, $routeIndex) {
        $this->router = $router;
        $this->routeIndex = $routeIndex;
    }
    
    /**
     * Add middleware to the route
     *
     * @param callable|string|array $middleware
     * @return $this
     */
    public function middleware($middleware) {
        if (is_array($middleware)) {
            foreach ($middleware as $m) {
                $this->router->addRouteMiddleware($this->routeIndex, $m);
            }
        } else {
            $this->router->addRouteMiddleware($this->routeIndex, $middleware);
        }
        return $this;
    }
    
    /**
     * Name the route
     *
     * @param string $name Route name
     * @return $this
     */
    public function name($name) {
        $this->router->nameRoute($this->routeIndex, $name);
        return $this;
    }
}
