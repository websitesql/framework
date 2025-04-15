<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Core;

use WebsiteSQL\Framework\Kernel;
use WebsiteSQL\Framework\Exceptions\GeneralException;
use WebsiteSQL\Router\Router;
use WebsiteSQL\Router\Container;
use WebsiteSQL\Router\Strategy\ApiStrategy;
use WebsiteSQL\Router\Factory\ResponseFactory;

class App extends Kernel
{

    /**
     * This object holds the Router class
     * 
     * @var Router
     */
    private Router $router;

	/**
	 * This object holds the Container class
	 * 
	 * @var Container
	 */
	private Container $container;

    /**
     * Constructor
     */
    public function __construct()
    {
		parent::__construct();

		// Create the router
		$this->router = new Router();

		// Create the container
		$this->container = new Container();

        // Register this instance in the container
        $this->container->add(App::class, $this);
    }

    /*
     * This method serves the application
     * 
     * @return void
     */
    public function serve(): void
    {
        // Hide default PHP errors
        error_reporting(0);
        ini_set('display_errors', '0');

		// Remove X-Powered-By header
        header_remove('X-Powered-By');

        // Create the response factory
        $responseFactory = new ResponseFactory();

        // Create the API strategy
        $apiStrategy = new ApiStrategy(
			$responseFactory,
			$this->getConfig()->get('app.env') === 'development' ? JSON_PRETTY_PRINT : 0,
			$this->getConfig()->get('app.debug')
		);

		// Set cors config if enabled
        if ($this->getConfig()->get('cors.enabled')) {
            $apiStrategy->corsConfig([
                'allowedOrigins' => array_map('trim', explode(',', $this->getConfig()->get('cors.allow_origin'))),
                'allowedMethods' => array_map('trim', explode(',', $this->getConfig()->get('cors.allow_methods'))),
                'exposedHeaders' => array_map('trim', explode(',', $this->getConfig()->get('cors.expose_headers'))),
                'maxAge' => $this->getConfig()->get('cors.max_age'),
                'allowCredentials' => $this->getConfig()->get('cors.allow_credentials'),
            ]);
        }

		// Add the request to the container
        $apiStrategy->setContainer($this->container);

        // Set the API strategy
        $this->router->setStrategy($apiStrategy);

        // Import the routes file routes/api.php
		$routesFile = $this->getBasePath() . '/routes/api.php';
		if (file_exists($routesFile)) {
			require_once $routesFile;
		} else {
			throw new GeneralException("Routes file not found: $routesFile");
		}

        $this->router->serve();
    }

    /*
     * This method returns the router object
     * 
     * @return Router
     */
    public function getRouter(): Router
    {
        // Return the router object
        return $this->router;
    }
}