<?php declare(strict_types=1);

namespace WebsiteSQL\Router;

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
     * @param callable|string|array $middleware Middleware callable, name or array of middleware
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