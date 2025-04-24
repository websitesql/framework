<?php declare(strict_types=1);

namespace WebsiteSQL\Http;

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
    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route->matches($method, $uri)) {
                return $route->execute($uri);
            }
        }
        
        // No route found
        http_response_code(404);
        echo "404 Not Found";
    }
    
    public function url($name, $params = []) {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Route with name {$name} not found");
        }
        
        return $this->namedRoutes[$name]->generateUrl($params);
    }
}