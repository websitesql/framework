<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Router;

use League\Route\Router as LeagueRouter;
use \FastRoute\RouteCollector;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\Diactoros\ServerRequestFactory;

class Router extends LeagueRouter
{
	/**
	 * Router constructor.
	 */
	public function __construct(?RouteCollector $routeCollector = null)
	{
		parent::__construct($routeCollector);
	}

	/**
	 * This method serves the router.
	 */
	public function serve(): void
	{
		// Create the request object
        $request = ServerRequestFactory::fromGlobals(
            $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
        );

		// Dispatch the request
        $response = $this->dispatch($request);

        // Send the response with SapiEmitter
        (new SapiEmitter())->emit($response);
	}
}