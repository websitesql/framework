<?php declare(strict_types=1);

namespace AlanTiller\Framework\Providers;

use AlanTiller\Framework\Interfaces\RouterInterface;
use League\Route\RouteCollection;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Container\Container;
use AlanTiller\Framework\Strategy\ApiStrategy;
use Psr\Http\Message\ResponseFactoryInterface;
use Laminas\Diactoros\ResponseFactory;

class LeagueRouteRouterProvider implements RouterInterface
{
    private RouteCollection $router;
    private Container $container;
    private ApiStrategy $strategy;

    public function __construct(ApiStrategy $strategy = null)
    {
        $this->container = new Container();
        $this->router = new RouteCollection($this->container);

        $responseFactory = new ResponseFactory();

        $this->strategy = $strategy ?? new ApiStrategy($responseFactory);
        $strategy = new ApplicationStrategy;
        $strategy->setContainer($this->container);
        $this->router->setStrategy($this->strategy);
    }

    public function addRoute(string $method, string $route, callable|string $handler): void
    {
        $this->router->map($method, $route, $handler);
    }

    public function dispatch(): void
    {
        $request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );

        try {
            $response = $this->router->dispatch($request);

            (new SapiEmitter)->emit($response);
        } catch (\League\Route\Http\Exception\NotFoundException $e) {
            http_response_code(404);
            echo '404 Not Found';
        } catch (\Exception $e) {
            http_response_code(500);
            echo '500 Internal Server Error: ' . $e->getMessage();
        }
    }

    public function getStrategy(): ApiStrategy
    {
        return $this->strategy;
    }
}