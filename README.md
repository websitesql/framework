# AlanTiller Framework

A lightweight PHP framework designed for building APIs.

## Features

*   **Routing:** Uses `league/route` for flexible and powerful routing.
*   **Middleware:** Supports middleware for request processing.
*   **Configuration:** Loads configuration from PHP files and environment variables.
*   **Environment Variables:** Uses `vlucas/phpdotenv` for managing environment variables.
*   **Database:** Uses `catfan/medoo` for database interactions.
*   **API Strategy:** Includes an `ApiStrategy` for standardized JSON responses and error handling.
*   **CORS:** Configurable Cross-Origin Resource Sharing (CORS) support.
*   **Authentication:** Provides basic authentication functionality.
*   **Mailer:** Uses `phpmailer/phpmailer` for sending emails with SMTP, PHP mail, or logging options.
*   **Utilities:** Includes utility functions for generating random strings, hashing passwords, and slugifying strings.
*   **PSR-7 Compatible:** Uses PSR-7 interfaces for HTTP requests and responses.
*   **Testing:** Uses Pest for a more enjoyable and expressive testing experience.

## Installation

1.  **Clone the repository:**

    ```bash
    git clone [repository_url] alan-tiller-framework
    cd alan-tiller-framework
    ```

2.  **Install dependencies using Composer:**

    ```bash
    composer install
    ```

3.  **Configure environment variables:**

    *   Copy `.env.example` to `.env`:

        ```bash
        cp .env.example .env
        ```

    *   Edit `.env` to set your environment-specific values (database credentials, mail settings, etc.).

## Configuration

The framework uses configuration files located in the `config/` directory.

*   **`config/app.php`:** General application settings (timezone, debug mode).
*   **`config/database.php`:** Database connection settings.
*   **`config/routes.php`:** Route definitions.
*   **`config/cors.php`:** CORS configuration.
*   **`config/mail.php`:** Mailer settings.
*   **`config/auth.php`:** Authentication settings.

Environment variables override the values in the configuration files.

## Usage

1.  **Create an entry point (e.g., `public/index.php`):**

    ```php
    <?php

    use AlanTiller\Framework\Core\App;

    require_once __DIR__ . '/../vendor/autoload.php';

    $app = new App(__DIR__ . '/../'); // Pass the base path

    $app->init();

    // Load routes from config/routes.php
    $routes = require __DIR__ . '/../config/routes.php';
    $routes($app);

    $app->serve();
    ```

2.  **Define routes in `config/routes.php`:**

    ```php
    <?php

    use AlanTiller\Framework\Core\App;

    return function (App $app) {
        $router = $app->getRouter();

        $router->addRoute('GET', '/', function () {
            return ['message' => 'Hello, world!'];
        });

        $router->addRoute('GET', '/error', function () {
            throw new \Exception('This is a test error.');
        });
    };
    ```

3.  **Run the application:**

    Use a web server (e.g., PHP's built-in web server) to run the application:

    ```bash
    php -S localhost:8000 -t public
    ```

    Then, access the application in your browser at `http://localhost:8000`.

## Core Components

### `App` Class

The main application class located in `src/Core/App.php`.

*   **`__construct(string $basePath = null, LoggerInterface $logger = null)`:** Constructor.  Takes the base path and an optional logger instance.
*   **`init(array $config = [])`:** Initializes the application.  Loads configuration, connects to the database, initializes the router, etc.
*   **`serve()`:** Serves the application by dispatching the router.
*   **`getConfig()`:** Returns the `Config` instance.
*   **`getDatabase()`:** Returns the `Medoo` database instance.
*   **`getRouter()`:** Returns the `RouterInterface` instance.
*   **`getMail()`:** Returns the `MailInterface` instance.
*   **`getAuth()`:** Returns the `AuthenticationInterface` instance.
    *   `attempt(array $credentials)`: Attempts to authenticate a user with the given credentials.
    *   `user()`: Returns the currently authenticated user.
    *   `check()`: Checks if a user is authenticated.
    *   `logout()`: Logs out the current user.
*   **`getUser()`:** Returns the `UserInterface` instance.
    *   `find(int $id)`: Finds a user by ID.
    *   `findByCredentials(array $credentials)`: Finds a user by credentials.
    *   `create(array $data)`: Creates a new user.
    *   `update(int $id, array $data)`: Updates an existing user.
    *   `delete(int $id)`: Deletes a user.
*   **`getUtilities()`:** Returns the `UtilitiesInterface` instance.
    *   `generateRandomString(int $length = 16)`: Generates a random string.
    *   `hashPassword(string $password)`: Hashes a password.
    *   `verifyPassword(string $password, string $hash)`: Verifies a password against a hash.
    *   `slugify(string $string)`: Generates a URL-friendly slug from a string.
*   **`getLogger()`:** Returns the `LoggerInterface` instance.
*   **`getBasePath()`:** Returns the base path of the application.

### `Config` Class

The configuration class located in `src/Core/Config.php`.

*   **`__construct(string $configPath, array $initialConfig = [])`:** Constructor.  Takes the path to the configuration directory and an optional array of initial configuration values.
*   **`get(string $key, $default = null)`:** Returns the value of the configuration key.
*   **`set(string $key, mixed $value)`:** Sets the value of the configuration key.
*   **`all()`:** Returns all configuration values.

### `RouterInterface` and `LeagueRouteRouterProvider`

The `RouterInterface` (located in `src/Interfaces/RouterInterface.php`) defines the contract for routing. The `LeagueRouteRouterProvider` (located in `src/Providers/LeagueRouteRouterProvider.php`) implements the `RouterInterface` using `league/route`.

*   **`addRoute(string $method, string $route, callable|string $handler)`:** Adds a route to the router.
*   **`dispatch()`:** Dispatches the router and sends the response.

### `ApiStrategy`

The `ApiStrategy` (located in `src/Strategy/ApiStrategy.php`) provides a standardized way to handle API requests and responses.

*   **`corsConfig(array $config)`:** Configures CORS settings.
*   **`createErrorResponse(ResponseInterface $response, Throwable $exception)`:** Creates a standardized JSON error response.

### `AuthenticationInterface` and `AuthenticationProvider`

The `AuthenticationInterface` (located in `src/Interfaces/AuthenticationInterface.php`) defines the contract for authentication. The `AuthenticationProvider` (located in `src/Providers/AuthenticationProvider.php`) implements the `AuthenticationInterface`.

### `UserInterface` and `UserProvider`

The `UserInterface` (located in `src/Interfaces/UserInterface.php`) defines the contract for user management. The `UserProvider` (located in `src/Providers/UserProvider.php`) implements the `UserInterface`.

### `MailInterface` and `MailProvider`

The `MailInterface` (located in `src/Interfaces/MailInterface.php`) defines the contract for sending emails. The `MailProvider` (located in `src/Providers/MailProvider.php`) implements the `MailInterface` using `phpmailer/phpmailer`.

### `UtilitiesInterface` and `UtilitiesProvider`

The `UtilitiesInterface` (located in `src/Interfaces/UtilitiesInterface.php`) defines the contract for utility functions. The `UtilitiesProvider` (located in `src/Providers/UtilitiesProvider.php`) implements the `UtilitiesInterface`.

## Error Handling

The `ApiStrategy` provides a centralized way to handle exceptions and return standardized JSON error responses. In debug mode, the error response includes the exception trace. In production mode, the error response includes a generic error message.

## CORS Configuration

CORS can be configured in the `config/cors.php` file. The following settings are available:

*   **`allowedOrigins`:** An array of allowed origins (e.g., `['http://example.com', 'https://example.org']`) or `['*']` to allow all origins.
*   **`allowedMethods`:** An array of allowed HTTP methods (e.g., `['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']`).
*   **`allowedHeaders`:** An array of allowed HTTP headers (e.g., `['Content-Type', 'Authorization', 'Accept']`).
*   **`exposedHeaders`:** An array of headers that can be exposed to the client.
*   **`maxAge`:** The maximum age (in seconds) that the CORS preflight request can be cached.
*   **`allowCredentials`:** Whether to allow credentials (e.g., cookies) to be included in the request.

## Mail Configuration

The mailer can be configured in the `config/mail.php` file. The following settings are available:

*   **`driver`:** The mail driver to use (`smtp`, `mail`, or `log`).
*   **`host`:** The SMTP host.
*   **`port`:** The SMTP port.
*   **`username`:** The SMTP username.
*   **`password`:** The SMTP password.
*   **`encryption`:** The SMTP encryption type (`tls` or `ssl`).
*   **`from_address`:** The "from" address for emails.
*   **`from_name`:** The "from" name for emails.

## Authentication

The framework provides basic authentication functionality.

1.  **Configure the authentication settings in `config/auth.php`:**

    *   **`username_field`:** The name of the database column that stores the username (default: `email`).
    *   **`password_field`:** The name of the database column that stores the password (default: `password`).

2.  **Use the `Auth` facade to authenticate users:**

    ```php
    <?php

    use AlanTiller\Framework\Core\App;

    return function (App $app) {
        $router = $app->getRouter();

        $router->addRoute('POST', '/login', function ($request) use ($app) {
            $auth = $app->getAuth();
            $params = $request->getParsedBody();

            $user = $auth->attempt([
                'username' => $params['email'],
                'password' => $params['password'],
            ]);

            if ($user) {
                return ['message' => 'Login successful'];
            } else {
                return ['error' => 'Invalid credentials'];
            }
        });

        $router->addRoute('GET', '/me', function () use ($app) {
            $auth = $app->getAuth();
            $user = $auth->user();

            if ($user) {
                return ['user' => $user];
            } else {
                return ['error' => 'Unauthorized'];
            }
        });

        $router->addRoute('POST', '/logout', function () use ($app) {
            $auth = $app->getAuth();
            $auth->logout();

            return ['message' => 'Logout successful'];
        });
    };
    ```

## Testing

This framework uses [Pest](https://pestphp.com/) for testing.

1.  **Install Pest:**

    ```bash
    composer require --dev pestphp/pest
    ```

2.  **Initialize Pest:**

    ```bash
    ./vendor/bin/pest --init
    ```

3.  **Run Tests:**

    ```bash
    ./vendor/bin/pest
    ```

## Contributing

Please submit pull requests with detailed descriptions of the changes.

## License

This framework is open-sourced software licensed under the [MIT license](LICENSE).