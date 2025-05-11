<?php declare(strict_types=1);

namespace WebsiteSQL\Router;

use WebsiteSQL\WebsiteSQL;

class Router {
    /**
     * Routes collection
     *
     * @var array
     */
    protected $routes = [];
    
    /**
     * Parameter patterns
     *
     * @var array
     */
    protected $patterns = [];
    
    /**
     * Pre-route middleware
     *
     * @var array
     */
    protected $beforeMiddleware = [];
    
    /**
     * After-route middleware
     *
     * @var array
     */
    protected $afterMiddleware = [];
    
    /**
     * Named routes
     *
     * @var array
     */
    protected $namedRoutes = [];
    
    /**
     * Add a route
     *
     * @param string $method HTTP method
     * @param string $pattern URL pattern
     * @param callable|string $callback Route handler
     * @return \WebsiteSQL\Router\RouteDecorator
     */
    public function map($method, $pattern, $callback) {
        $route = [
            'pattern' => $pattern,
            'callback' => $callback,
            'method' => strtoupper($method),
            'middleware' => []
        ];
        
        $this->routes[] = $route;
        $routeIndex = count($this->routes) - 1;
        
        return new RouteDecorator($this, $routeIndex);
    }
    
    /**
     * Add a GET route
     *
     * @param string $pattern URL pattern
     * @param callable|string $callback Route handler
     * @return \WebsiteSQL\Router\RouteDecorator
     */
    public function get($pattern, $callback) {
        return $this->map('GET', $pattern, $callback);
    }
    
    /**
     * Add a POST route
     *
     * @param string $pattern URL pattern
     * @param callable|string $callback Route handler
     * @return \WebsiteSQL\Router\RouteDecorator
     */
    public function post($pattern, $callback) {
        return $this->map('POST', $pattern, $callback);
    }
    
    /**
     * Add a PUT route
     *
     * @param string $pattern URL pattern
     * @param callable|string $callback Route handler
     * @return \WebsiteSQL\Router\RouteDecorator
     */
    public function put($pattern, $callback) {
        return $this->map('PUT', $pattern, $callback);
    }
    
    /**
     * Add a DELETE route
     *
     * @param string $pattern URL pattern
     * @param callable|string $callback Route handler
     * @return \WebsiteSQL\Router\RouteDecorator
     */
    public function delete($pattern, $callback) {
        return $this->map('DELETE', $pattern, $callback);
    }
    
    /**
     * Add a PATCH route
     *
     * @param string $pattern URL pattern
     * @param callable|string $callback Route handler
     * @return \WebsiteSQL\Router\RouteDecorator
     */
    public function patch($pattern, $callback) {
        return $this->map('PATCH', $pattern, $callback);
    }
    
    /**
     * Add an OPTIONS route
     *
     * @param string $pattern URL pattern
     * @param callable|string $callback Route handler
     * @return \WebsiteSQL\Router\RouteDecorator
     */
    public function options($pattern, $callback) {
        return $this->map('OPTIONS', $pattern, $callback);
    }
    
    /**
     * Add a route for any HTTP method
     *
     * @param string $pattern URL pattern
     * @param callable|string $callback Route handler
     * @return \WebsiteSQL\Router\RouteDecorator
     */
    public function any($pattern, $callback) {
        return $this->map('GET|POST|PUT|DELETE|PATCH|OPTIONS|HEAD', $pattern, $callback);
    }
    
    /**
     * Add a route that responds to multiple HTTP methods
     *
     * @param array $methods HTTP methods
     * @param string $pattern URL pattern
     * @param callable|string $callback Route handler
     * @return \WebsiteSQL\Router\RouteDecorator
     */
    public function methods(array $methods, $pattern, $callback) {
        return $this->map(implode('|', array_map('strtoupper', $methods)), $pattern, $callback);
    }
    
    /**
     * Add a parameter pattern
     *
     * @param string $name Parameter name
     * @param string $pattern Regex pattern
     * @return $this
     */
    public function pattern($name, $pattern) {
        $this->patterns[$name] = $pattern;
        return $this;
    }
    
    /**
     * Add global "before" middleware
     *
     * @param callable|string $middleware Middleware callback
     * @return $this
     */
    public function before($middleware) {
        $this->beforeMiddleware[] = $middleware;
        return $this;
    }
    
    /**
     * Add global "after" middleware
     *
     * @param callable|string $middleware Middleware callback
     * @return $this
     */
    public function after($middleware) {
        $this->afterMiddleware[] = $middleware;
        return $this;
    }
    
    /**
     * Set a response as JSON
     *
     * @param mixed $data Data to encode as JSON
     * @return Response
     */
    public function json($data) {
        $response = new Response();
        return $response->json($data);
    }
    
    /**
     * Render a view
     *
     * @param string $view Path to the view file
     * @param array $data Data to pass to the view
     * @return Response
     */
    public function render($view, $data = []) {
        $response = new Response();
        return $response->render($view, $data);
    }
    
    /**
     * Redirect to URL
     *
     * @param string $url URL to redirect to
     * @param int $code HTTP status code
     * @return Response
     */
    public function redirect($url, $code = 302) {
        $response = new Response();
        return $response->redirect($url, $code);
    }
    
    /**
     * Name a route
     *
     * @param int $routeIndex Route index in the routes array
     * @param string $name Route name
     * @return void
     */
    public function nameRoute($routeIndex, $name) {
        $this->namedRoutes[$name] = $routeIndex;
    }
    
    /**
     * Add middleware to a specific route
     *
     * @param int $routeIndex Route index
     * @param callable|string $middleware Middleware callback or name
     * @return void
     */
    public function addRouteMiddleware($routeIndex, $middleware) {
        $this->routes[$routeIndex]['middleware'][] = $middleware;
    }
    
    /**
     * Generate a URL for a named route
     *
     * @param string $name Route name
     * @param array $params Route parameters
     * @return string Generated URL
     * @throws \Exception If route not found
     */
    public function urlFor($name, array $params = []) {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Named route not found: $name");
        }
        
        $route = $this->routes[$this->namedRoutes[$name]];
        $url = $route['pattern'];
        
        // Replace named parameters
        foreach ($params as $paramName => $paramValue) {
            $url = str_replace(':' . $paramName, $paramValue, $url);
        }
        
        return $url;
    }
    
    /**
     * Get all routes
     * 
     * @return array
     */
    public function getRoutes() {
        return $this->routes;
    }
    
    /**
     * Get a named route
     * 
     * @param string $name Route name
     * @return array|null Route configuration or null if not found
     */
    public function getNamedRoute($name) {
        if (!isset($this->namedRoutes[$name])) {
            return null;
        }
        
        return $this->routes[$this->namedRoutes[$name]];
    }
    
    /**
     * Group routes with shared attributes
     * 
     * @param string $prefix URL prefix for all routes in the group
     * @param callable $callback Function to define routes in the group
     * @return void
     */
    public function group($prefix, callable $callback) {
        // Store current state to restore after group processing
        $currentRoutes = $this->routes;
        $currentNamedRoutes = $this->namedRoutes;
        
        // Clear routes to add group routes
        $this->routes = [];
        $this->namedRoutes = [];
        
        // Call the group definition function
        $callback($this);
        
        // Apply prefix to all routes defined in the group
        $groupRoutes = $this->routes;
        $groupNamedRoutes = $this->namedRoutes;
        
        $this->routes = $currentRoutes;
        $this->namedRoutes = $currentNamedRoutes;
        
        // Add prefixed routes to the main routes collection
        foreach ($groupRoutes as $route) {
            $route['pattern'] = $prefix . '/' . ltrim($route['pattern'], '/');
            $this->routes[] = $route;
            
            // Update named routes
            $routeIndex = count($this->routes) - 1;
            foreach ($groupNamedRoutes as $name => $index) {
                if ($index === count($groupRoutes) - 1) {
                    $this->namedRoutes[$name] = $routeIndex;
                }
            }
        }
    }
    
    /**
     * Dispatch the router
     *
     * @param string|null $method HTTP method (uses current request method if null)
     * @param string|null $uri Request URI (uses current request URI if null)
     * @return mixed
     */
    public function dispatch($method = null, $uri = null) {
        $method = $method ?: $_SERVER['REQUEST_METHOD'];
        
        if ($uri === null) {
            $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $uri = '/' . trim($requestUri, '/');
        }
        
        // Create request and response objects
        $request = WebsiteSQL::request();
        $response = WebsiteSQL::response();
        
        // Execute "before" middleware
        foreach ($this->beforeMiddleware as $middleware) {
            $middlewareResult = $this->executeMiddleware($middleware, $request, $response);
            if ($middlewareResult instanceof Response) {
                return $middlewareResult->send();
            }
        }
        
        // Match the route
        $routeFound = false;
        $result = null;
        $methodNotAllowed = false;
        $allowedMethods = [];
        
        foreach ($this->routes as $route) {
            $pattern = $route['pattern'];
            
            // Apply parameter patterns
            foreach ($this->patterns as $name => $regex) {
                $pattern = str_replace(':' . $name, '(' . $regex . ')', $pattern);
            }
            
            // Replace standard parameters with regex
            $pattern = preg_replace('/:([a-zA-Z0-9_]+)/', '([^/]+)', $pattern);
            
            // Check if route matches
            if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
                // If the method matches, handle the route
                if (strpos($route['method'], $method) !== false) {
                    $routeFound = true;
                    
                    // Extract parameters
                    array_shift($matches); // Remove first match (the full match)
                    
                    // Process route with middleware
                    $routeMiddleware = $this->processRouteMiddleware($route['middleware']);
                    $routeHandler = $this->createRouteHandler($route['callback']);
                    
                    if (!empty($routeMiddleware)) {
                        $result = WebsiteSQL::middleware()->process(
                            $routeMiddleware, 
                            $routeHandler, 
                            $request, 
                            $response, 
                            $matches
                        );
                    } else {
                        // Execute the route callback directly if no middleware
                        $result = call_user_func_array($routeHandler, array_merge([$request, $response], $matches));
                    }
                    
                    break;
                } else {
                    // If the URL pattern matches but the method doesn't, it's a method not allowed
                    $methodNotAllowed = true;
                    
                    // Collect allowed methods for this route
                    $routeMethods = explode('|', $route['method']);
                    foreach ($routeMethods as $routeMethod) {
                        if (!in_array($routeMethod, $allowedMethods)) {
                            $allowedMethods[] = $routeMethod;
                        }
                    }
                }
            }
        }
        
        // If no route was found, return a 404 or 405 response
        if (!$routeFound) {
            if ($methodNotAllowed && !empty($allowedMethods)) {
                // Return 405 Method Not Allowed with the allowed methods header
                return $response->status(405)->header('Allow', implode(', ', $allowedMethods))->json([
                    'code' => 405,
                    'error' => [
                        'type' => 'METHOD_NOT_ALLOWED',
                        'message' => 'The ' . $method . ' method is not supported for this route. Must be one of: ' . implode(', ', $allowedMethods)
                    ]
                ])->send();
            } else {
                // Return 404 Not Found
                return $response->status(404)->json([
                    'code' => 404,
                    'error' => [
                        'type' => 'NOT_FOUND',
                        'message' => 'The requested URL was not found on this server.'
                    ]
                ])->send();
            }
        }
        
        // Execute "after" middleware
        foreach ($this->afterMiddleware as $middleware) {
            $middlewareResult = $this->executeMiddleware($middleware, $request, $response);
            if ($middlewareResult instanceof Response) {
                return $middlewareResult->send();
            }
        }
        
        // If the result is already a Response object, send it
        if ($result instanceof Response) {
            return $result->send();
        }
        
        // Otherwise, set the body and send
        return $response->body((string) $result)->send();
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
        } elseif (is_string($middleware) && strpos($middleware, '@') !== false) {
            list($class, $method) = explode('@', $middleware);
            $instance = new $class();
            return call_user_func([$instance, $method], $request, $response);
        }
        return null;
    }
    
    /**
     * Process route middleware and convert to array of middleware names
     *
     * @param array $middleware Array of middleware handlers or names
     * @return array Array of middleware names
     */
    protected function processRouteMiddleware(array $middleware) {
        $result = [];
        
        foreach ($middleware as $m) {
            if (is_string($m) && !strpos($m, '@')) {
                // If it's just a string without @, it's a middleware name
                $result[] = $m;
            } else {
                // For callables or class@method format, register it with a generated name
                $name = 'middleware_' . md5(serialize($m));
                WebsiteSQL::middleware()->register($name, $m);
                $result[] = $name;
            }
        }
        
        return $result;
    }
    
    /**
     * Create a callable route handler from a callback or controller@method string
     *
     * @param callable|string $callback
     * @return callable
     */
    protected function createRouteHandler($callback) {
        if (is_callable($callback)) {
            return $callback;
        } elseif (is_string($callback) && strpos($callback, '@') !== false) {
            return function($request, $response, ...$params) use ($callback) {
                list($class, $method) = explode('@', $callback);
                $instance = new $class();
                return call_user_func_array([$instance, $method], array_merge([$request, $response], $params));
            };
        }
        
        throw new \Exception("Invalid route callback");
    }
}