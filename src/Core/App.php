<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Core;

use Exception;
use WebsiteSQL\Framework\Kernel;
use WebsiteSQL\Framework\Router\Router;
use WebsiteSQL\Framework\Router\Container;
use WebsiteSQL\Framework\Router\Strategy\ApiStrategy;
use WebsiteSQL\Framework\Router\Factory\ResponseFactory;
use WebsiteSQL\Framework\Exception\GeneralException;
use WebsiteSQL\Framework\Auth\User;
use WebsiteSQL\Framework\Auth\Auth;

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
	 * This object holds the User class
	 * 
	 * @var User
	 */
	private User $user;

	/**
	 * This object holds the Auth class
	 * 
	 * @var Auth
	 */
	private Auth $auth;

    /**
     * Constructor
     */
    public function __construct()
    {
		parent::__construct();

		// Create the user object
		$this->user = new User($this);

		// Create the auth object
		$this->auth = new Auth($this);

		// Create the router
		$this->router = new Router();

		// Create the container
		$this->container = new Container();

        // Register this instance in the container
        $this->container->add(App::class, $this);

		// Serve the application
		$this->serve();
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
		$this->importRoutes();

		// Set the default route
        $this->router->serve();
    }

	/**
	 * This method imports routes
	 * 
	 * @return void
	 */
	private function importRoutes(): void
	{
		// Define the router as a local variable
		$app = $this->router;

		// Require the routes file
		try {
			require_once $this->getBasePath() . '/routes/api.php';
		} catch (Exception $e) {
			throw new GeneralException("Failed to import the routes file", 0, $e);
		}
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

	/**
	 * This method returns the user object
	 * 
	 * @return User
	 */
	public function getUser(): User
	{
		// Return the user object
		return $this->user;
	}

	/**
	 * This method returns the auth object
	 * 
	 * @return Auth
	 */
	public function getAuth(): Auth
	{
		// Return the auth object
		return $this->auth;
	}
}