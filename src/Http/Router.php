<?php declare(strict_types=1);

namespace WebsiteSQL\Http;

use WebsiteSQL\Http\Message\ResponseInterface;
use WebsiteSQL\Http\Message\ServerRequestInterface;
use WebsiteSQL\WebsiteSQL;

class Router {
    private $routes = [];
    private $namedRoutes = [];
    
    public function add($method, $path, $callback) {
        $route = new Route($method, $path, $callback);
        $this->routes[] = $route;
        return $route;
    }
    
    public function addNamed($name, $route) {
        $this->namedRoutes[$name] = $route;
    }
    
    public function dispatch(ServerRequestInterface $request = null): ResponseInterface {
        if ($request === null) {
            $request = WebsiteSQL::http()->createServerRequestFromGlobals();
        }
        
        $method = $request->getMethod();
        $uri = $request->getRequestTarget();
        
        try {
            foreach ($this->routes as $route) {
                if ($route->matches($method, $uri)) {
                    // The route is responsible for:
                    // 1. Extracting URI parameters and adding them to the request
                    // 2. Running middleware before the controller
                    // 3. Executing the controller with the request and a fresh response
                    // 4. Returning the final response
                    return $route->execute($request, WebsiteSQL::http()->createResponse());
                }
            }
            
            // No route found
            return WebsiteSQL::http()->jsonResponse(
                [
                    'error' => [
                        'message' => 'The requested resource could not be found.',
                        'type' => 'RESOURCE_NOT_FOUND',
                        'code' => 404
                    ]
                ],
                404
            );
        } catch (\Exception $e) {
            // Check if the user has specified a debug flag
            $debug = WebsiteSQL::config()->get('app.debug', false);

            // Handle other exceptions as needed
            return WebsiteSQL::http()->jsonResponse(
                [
                    'error' => [
                        'message' => $debug ? $e->getMessage() : 'An internal server error occurred.',
                        'type' => 'INTERNAL_SERVER_ERROR',
                        'code' => 500
                    ],
                ],
                500
            );
        }
    }
    
    public function url($name, $params = []) {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Route with name {$name} not found");
        }
        
        return $this->namedRoutes[$name]->generateUrl($params);
    }
}