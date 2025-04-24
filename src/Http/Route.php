<?php declare(strict_types=1);

namespace WebsiteSQL\Http;

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
    
    public function execute($uri) {
        // Run middleware
        foreach ($this->middleware as $middleware) {
            $result = WebsiteSQL::middleware()->run($middleware);
            if ($result === false) {
                http_response_code(403);
                echo "Forbidden";
                return;
            }
        }
        
        // Extract parameters
        $params = $this->extractParams($uri);
        
        // Execute callback
        if (is_callable($this->callback)) {
            return call_user_func_array($this->callback, $params);
        } elseif (is_string($this->callback)) {
            list($controller, $method) = explode('@', $this->callback);
            $controllerInstance = new $controller();
            return call_user_func_array([$controllerInstance, $method], $params);
        }
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
