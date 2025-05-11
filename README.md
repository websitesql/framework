# WebsiteSQL Framework

A lightweight, flexible PHP framework with a simple and intuitive API inspired by Flight PHP.

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Custom Instance Registration](#custom-instance-registration)
- [Routing](#routing)
- [Middleware](#middleware)
- [Working with Requests & Responses](#working-with-requests--responses)
- [Database Operations](#database-operations)
- [Migrations](#migrations)
- [Cron Management](#cron-management)
- [Project Structure](#project-structure)
- [License](#license)

## Installation

### Requirements
- PHP 8.2 or later
- Composer
- PDO PHP extension

### Installing via Composer

```bash
composer require websitesql/framework
```

### Creating a New Project

You can also create a new project using the WebsiteSQL skeleton:

```bash
composer create-project websitesql/websitesql my-project
```

## Quick Start

```php
<?php

// Require autoloader
require 'vendor/autoload.php';

use WebsiteSQL\WebsiteSQL;

// Define config
WebsiteSQL::config()->add([
    'db' => [
        'host' => 'localhost',
        'database' => 'my_database',
        'user' => 'root',
        'password' => 'secret'
    ]
]);

// Configure DB
WebsiteSQL::db()->config([
    'host' => WebsiteSQL::config()->get('db.host'),
    'database' => WebsiteSQL::config()->get('db.database'),
    'user' => WebsiteSQL::config()->get('db.user'),
    'password' => WebsiteSQL::config()->get('db.password'),
]);

// Define a simple route
WebsiteSQL::router()->get('/', function($request, $response) {
    return $response->html('Hello, WebsiteSQL!');
});

// Start the application
WebsiteSQL::start();
```

## Configuration

WebsiteSQL provides a flexible configuration system with dot notation support:

```php
// Add configuration
WebsiteSQL::config()->add([
    'app' => [
        'name' => 'My App',
        'debug' => true
    ]
]);

// Get configuration values
$appName = WebsiteSQL::config()->get('app.name');
$debug = WebsiteSQL::config()->get('app.debug');

// Set configuration values
WebsiteSQL::config()->set('app.name', 'New App Name');
```

### Configuration Files

For larger applications, it's recommended to organize your configuration in files:

```php
// config/app.php
return [
    'app' => [
        'name' => 'WebsiteSQL Application',
        'debug' => true,
    ],
    'db' => [
        'host' => 'localhost',
        'database' => 'websitesql',
        'user' => 'root',
        'password' => '',
    ]
];

// In your index.php
$config = require __DIR__ . '/config/app.php';
WebsiteSQL::config()->add($config);
```

## Custom Instance Registration

WebsiteSQL allows you to register custom instances or classes to be accessible through the WebsiteSQL facade:

```php
// Register a class - it will be instantiated when first called
WebsiteSQL::register('mail', '\App\Services\Mailer');

// Later use it
WebsiteSQL::mail()->send($message);

// Register a pre-configured instance
$logger = new \App\Services\Logger('/path/to/logs');
WebsiteSQL::register('logger', $logger);

// Later use it
WebsiteSQL::logger()->debug('Something happened');

// Register a closure
WebsiteSQL::register('generateId', function() {
    return uniqid('prefix_');
});

// Later use it
$id = WebsiteSQL::generateId();
```

The registration system handles three different scenarios:

1. **Class Names**: If you register a class name (string), it will be instantiated on first access
2. **Callables**: If you register a callable, it will be executed with any arguments passed
3. **Object Instances**: If you register an object instance, it will be returned as is

This allows you to integrate any service or component into your application and access it through the central WebsiteSQL facade.

### Complete Example: Implementing a Simple API Service

Here's a complete example showing how to create and register a simple API service:

```php
<?php
// app/Services/WeatherService.php
namespace App\Services;

class WeatherService {
    private $apiKey;
    private $baseUrl = 'https://api.weather.example.com';
    
    public function __construct($apiKey = null) {
        $this->apiKey = $apiKey ?? WebsiteSQL::config()->get('services.weather.key');
    }
    
    public function getCurrentWeather($city) {
        // In a real application, you would make an API call here
        $url = "{$this->baseUrl}/current?city={$city}&key={$this->apiKey}";
        
        // Simulate API call
        $response = $this->makeApiCall($url);
        
        return $response;
    }
    
    public function getForecast($city, $days = 3) {
        $url = "{$this->baseUrl}/forecast?city={$city}&days={$days}&key={$this->apiKey}";
        
        // Simulate API call
        $response = $this->makeApiCall($url);
        
        return $response;
    }
    
    private function makeApiCall($url) {
        // In a real app, use curl or Guzzle to make the API call
        // This is just a simulation
        return [
            'success' => true,
            'data' => [
                'temperature' => rand(0, 35),
                'conditions' => ['Sunny', 'Cloudy', 'Rainy', 'Snowy'][rand(0, 3)],
                'humidity' => rand(30, 90)
            ]
        ];
    }
}

// Register the service in your bootstrap file or where appropriate
WebsiteSQL::register('weather', '\App\Services\WeatherService');

// Now use it in your controllers or anywhere in your application
WebsiteSQL::router()->get('/weather/:city', function($request, $response, $city) {
    $weatherData = WebsiteSQL::weather()->getCurrentWeather($city);
    
    return $response->json($weatherData);
});

WebsiteSQL::router()->get('/forecast/:city/:days?', function($request, $response, $city, $days = 3) {
    $forecastData = WebsiteSQL::weather()->getForecast($city, $days);
    
    return $response->json($forecastData);
});
```

## Routing

WebsiteSQL offers a simple and intuitive routing system inspired by Flight PHP:

### Basic Routes

```php
// Simple route with a closure
WebsiteSQL::router()->get('/hello', function($request, $response) {
    return $response->html('<h1>Hello World!</h1>');
});

// Route with controller
WebsiteSQL::router()->get('/users', 'App\\Controllers\\UserController@index');

// Route with parameters
WebsiteSQL::router()->get('/users/:id', function($request, $response, $id) {
    // $id parameter is automatically passed to the callback
    return $response->html("<h1>User ID: $id</h1>");
});

// Named routes
WebsiteSQL::router()->get('/about', function($request, $response) {
    return $response->html('<h1>About Us</h1>');
})->name('about');
```

### Route with Middleware

```php
// Apply middleware to a route
WebsiteSQL::router()->get('/dashboard', function($request, $response) {
    return $response->html('<h1>Dashboard</h1>');
})->middleware('authenticate');
```

### HTTP Methods

WebsiteSQL supports all standard HTTP methods:

```php
// GET request
WebsiteSQL::router()->get('/users', 'UserController@index');

// POST request
WebsiteSQL::router()->post('/users', 'UserController@store');

// PUT request
WebsiteSQL::router()->put('/users/:id', 'UserController@update');

// DELETE request
WebsiteSQL::router()->delete('/users/:id', 'UserController@destroy');

// PATCH request
WebsiteSQL::router()->patch('/users/:id', 'UserController@update');

// Any HTTP method
WebsiteSQL::router()->any('/api', function($request, $response) {
    return $response->json(['message' => 'API endpoint']);
});

// Multiple HTTP methods
WebsiteSQL::router()->methods(['GET', 'POST'], '/form', 'FormController@handle');
```

### Route Parameters

```php
// Basic parameter
WebsiteSQL::router()->get('/users/:id', function($request, $response, $id) {
    return $response->html("User ID: $id");
});

// Custom parameter pattern
WebsiteSQL::router()->pattern('id', '[0-9]+');
WebsiteSQL::router()->get('/users/:id', function($request, $response, $id) {
    // $id will only match numbers
    return $response->html("User ID: $id");
});
```

### Route Groups

```php
// Group routes with a common prefix
WebsiteSQL::router()->group('/admin', function($router) {
    $router->get('/dashboard', function($request, $response) {
        return $response->html('Admin Dashboard');
    });
    
    $router->get('/users', function($request, $response) {
        return $response->html('Admin Users');
    });
});
```

### URL Generation

```php
// Define a named route
WebsiteSQL::router()->get('/profile/:username', function($request, $response, $username) {
    return $response->html("Profile of $username");
})->name('profile');

// Generate a URL from a named route
$url = WebsiteSQL::router()->urlFor('profile', ['username' => 'john']);
// $url will be "/profile/john"
```

## Middleware

Middleware provides a convenient mechanism for filtering HTTP requests and for processing request/response cycles. With WebsiteSQL, you can register middleware by name and then use it throughout your application.

### Registering Named Middleware

```php
// Register a middleware with a name
WebsiteSQL::middleware()->register('auth', function($request, $response) {
    // Check if user is authenticated
    if (!isset($_SESSION['user_id'])) {
        return $response->redirect('/login');
    }
    
    // Continue the request cycle
    return $request->process();
});

// Register a middleware class
WebsiteSQL::middleware()->register('log', 'App\\Middleware\\LogMiddleware');
```

### Using Middleware with Routes

```php
// Apply named middleware to a route
WebsiteSQL::router()->get('/dashboard', function($request, $response) {
    return $response->json([
        'message' => 'Welcome to the dashboard!'
    ]);
})->middleware('auth');

// Apply multiple middleware to a route
WebsiteSQL::router()->get('/admin/settings', function($request, $response) {
    return $response->html('<h1>Admin Settings</h1>');
})->middleware(['auth', 'admin']);
```

### Middleware with Process Control

One of the powerful features of the WebsiteSQL middleware system is the ability to process the entire request/response cycle and modify the response:

```php
WebsiteSQL::middleware()->register('api-logger', function($request, $response) {
    // Log the request
    $startTime = microtime(true);
    
    // Process the route and downstream middleware
    $response = $request->process();
    
    // Log the response time
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    
    // Add execution time to response header
    $response->header('X-Execution-Time', $executionTime . 'ms');
    
    return $response;
});
```

### Early Response from Middleware

Middleware can also intercept and short-circuit the request processing:

```php
WebsiteSQL::middleware()->register('rate-limiter', function($request, $response) {
    $ip = $request->ip();
    
    if (isRateLimitExceeded($ip)) {
        // Return response directly, don't process the route
        return $response->status(429)->json([
            'error' => 'Too many requests',
            'retry_after' => 60
        ]);
    }
    
    // Continue processing the request
    return $request->process();
});
```

### Global Middleware

```php
// Apply middleware to all routes
WebsiteSQL::router()->before(function($request, $response) {
    // Execute before each request
    $response->header('X-Frame-Options', 'DENY');
});

// Execute after route processing
WebsiteSQL::router()->after(function($request, $response) {
    // Execute after each request
    $response->header('X-Powered-By', 'WebsiteSQL');
    return $response;
});
```

### CORS Middleware

Here's an example of implementing CORS (Cross-Origin Resource Sharing) middleware to handle OPTIONS requests and add necessary headers:

```php
// Define CORS middleware class
class CorsMiddleware {
    public function handle($request, $response) {
        // Allow requests from any origin
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        
        // Handle preflight OPTIONS requests
        if ($request->method() === 'OPTIONS') {
            return $response->status(200)->send();
        }
        
        // Continue processing the request
        return $request->process();
    }
}

// Register the middleware class
WebsiteSQL::middleware()->register('cors', 'CorsMiddleware');

// Apply CORS middleware to all API routes
WebsiteSQL::router()->group('/api', function($router) {
    $router->get('/users', function($request, $response) {
        return $response->json(['users' => ['John', 'Jane']]);
    })->middleware('cors');
});
```

### Middleware Class Implementation

For more complex middleware, using classes provides better organization:

```php
<?php
namespace App\Middleware;

use WebsiteSQL\Router\Request;
use WebsiteSQL\Router\Response;

class AuthMiddleware {
    public function __invoke(Request $request, Response $response) {
        if (!isset($_SESSION['user_id'])) {
            return $response->redirect('/login');
        }
        
        // Continue processing
        return $request->process();
    }
}

// Register the middleware
WebsiteSQL::middleware()->register('auth', new \App\Middleware\AuthMiddleware());
```

## Working with Requests & Responses

### Request Information

```php
WebsiteSQL::router()->get('/info', function($request, $response) {
    // Get HTTP method
    $method = $request->method();
    
    // Get path
    $path = $request->path();
    
    // Get query parameters
    $allParams = $request->query();
    $page = $request->query('page', 1); // With default
    
    // Get client IP
    $ip = $request->ip();
    
    // Check if AJAX request
    $isAjax = $request->isAjax();
    
    // Check request type
    $isGet = $request->isGet();
    $isPost = $request->isPost();
    
    return $response->json([
        'method' => $method,
        'path' => $path,
        'params' => $allParams,
        'page' => $page,
        'ip' => $ip,
        'isAjax' => $isAjax,
        'isGet' => $isGet,
        'isPost' => $isPost
    ]);
});
```

### Processing POST Data

```php
WebsiteSQL::router()->post('/users', function($request, $response) {
    // Get all form/JSON data
    $data = $request->input();
    
    // Get a specific field (with default value if not present)
    $name = $request->input('name', '');
    $email = $request->input('email', '');
    
    // Validate data
    if (empty($name) || empty($email)) {
        return $response->status(400)->json([
            'error' => 'Name and email are required'
        ]);
    }
    
    // Create user in database
    $userId = WebsiteSQL::db()->insert('users', [
        'name' => $name,
        'email' => $email
    ]);
    
    // Return success response
    return $response->status(201)->json([
        'id' => $userId,
        'message' => 'User created successfully'
    ]);
});
```

### File Uploads

```php
WebsiteSQL::router()->post('/upload', function($request, $response) {
    // Get uploaded file
    $file = $request->file('avatar');
    
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        // Move file to permanent location
        $targetPath = 'uploads/' . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $targetPath);
        
        return $response->json([
            'success' => true,
            'path' => $targetPath
        ]);
    }
    
    return $response->status(400)->json([
        'error' => 'Upload failed'
    ]);
});
```

### Response Types

```php
// HTML Response
WebsiteSQL::router()->get('/html', function($request, $response) {
    return $response->html('<h1>Hello World!</h1>');
});

// JSON Response
WebsiteSQL::router()->get('/json', function($request, $response) {
    return $response->json([
        'message' => 'Hello World!',
        'timestamp' => time()
    ]);
});

// Plain text response
WebsiteSQL::router()->get('/text', function($request, $response) {
    return $response->text('Hello World!');
});

// File download
WebsiteSQL::router()->get('/download', function($request, $response) {
    return $response->download('/path/to/file.pdf', 'document.pdf');
});

// Redirect
WebsiteSQL::router()->get('/redirect', function($request, $response) {
    return $response->redirect('/dashboard');
});
```

### Template Rendering

```php
WebsiteSQL::router()->get('/page', function($request, $response) {
    return $response->render('views/page.php', [
        'title' => 'My Page',
        'content' => 'Hello from WebsiteSQL!'
    ]);
});
```

## Database Operations

### Connection Setup

```php
WebsiteSQL::db()->config([
    'host' => 'localhost',
    'database' => 'my_database',
    'user' => 'root',
    'password' => 'secret'
]);
```

### Basic CRUD Operations

```php
// Select all users
$users = WebsiteSQL::db()->get('users');

// Select specific columns
$users = WebsiteSQL::db()->get('users', 'id, first_name, email');

// Select with WHERE clause
$user = WebsiteSQL::db()->get('users', '*', ['id' => 1]);

// Insert a new record
$userId = WebsiteSQL::db()->insert('users', [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com',
    'password' => password_hash('secret', PASSWORD_DEFAULT)
]);

// Update a record
WebsiteSQL::db()->update('users', 
    ['first_name' => 'Jane'], 
    ['id' => 1]
);

// Delete a record
WebsiteSQL::db()->delete('users', ['id' => 1]);
```

### Query Builder

```php
// Complex query with the query builder
$activeUsers = WebsiteSQL::db()->query()
    ->table('users')
    ->select('id, first_name, email')
    ->where('status', 'active')
    ->where('created_at', '>', '2023-01-01')
    ->get();

// First result only
$user = WebsiteSQL::db()->query()
    ->table('users')
    ->where('email', 'john@example.com')
    ->first();
```

## Migrations

WebsiteSQL provides a robust migration system for managing your database schema:

### Defining Migrations

```php
// Inline migration definition
WebsiteSQL::db()->migration('Version00001', function($db) {
    // Up function
    $db->create('users', [
        "id" => [
            "INT",
            "NOT NULL",
            "AUTO_INCREMENT",
            "PRIMARY KEY"
        ],
        "first_name" => [
            "VARCHAR(30)",
            "NOT NULL"
        ],
        "email" => [
            "VARCHAR(100)",
            "NOT NULL",
            "UNIQUE"
        ]
    ]);
}, function($db) {
    // Down function
    $db->drop('users');
});

// Using controller classes
WebsiteSQL::db()->migration(
    'Version00002', 
    'App\\Database\\Migrations\\Version00002@up', 
    'App\\Database\\Migrations\\Version00002@down'
);
```

### Migration Command Line Interface

WebsiteSQL includes a command-line tool for managing migrations:

```bash
# Run all pending migrations
php bin/websitesql migrate

# Run a specific number of pending migrations
php bin/websitesql migrate 2

# Rollback the last batch of migrations
php bin/websitesql rollback

# Rollback a specific number of batches
php bin/websitesql rollback 3

# Rollback all migrations
php bin/websitesql reset

# Rollback all migrations and run them again
php bin/websitesql refresh

# Show migration status
php bin/websitesql status
```

### Migration Methods

WebsiteSQL provides various methods for schema manipulation:

```php
// Create a table
$db->create('table_name', [
    'column_name' => ['definition', 'parts']
]);

// Drop a table
$db->drop('table_name');

// Add a column
$db->addColumn('table_name', 'column_name', ['VARCHAR(255)', 'NOT NULL']);

// Drop a column
$db->dropColumn('table_name', 'column_name');

// Add an index
$db->addIndex('table_name', 'index_name', 'column_name');
$db->addIndex('table_name', 'composite_idx', ['col1', 'col2'], 'UNIQUE');

// Drop an index
$db->dropIndex('table_name', 'index_name');
```

### Migration File Organization

```
my-project/
├── database/
│   └── migrations/
│       ├── Version00001.php
│       └── Version00002.php
```

Example migration file (Version00001.php):

```php
<?php
namespace App\Database\Migrations;

use WebsiteSQL\WebsiteSQL;

// Register the migration
WebsiteSQL::db()->migration('Version00001', function($db) {
    // Up migration logic
    $db->create('users', [
        "id" => ["INT", "NOT NULL", "AUTO_INCREMENT", "PRIMARY KEY"],
        "name" => ["VARCHAR(100)", "NOT NULL"]
    ]);
}, function($db) {
    // Down migration logic
    $db->drop('users');
});
```

## Cron Management

WebsiteSQL provides a robust cron job management system that allows you to schedule tasks using standard cron expressions.

### Scheduling Cron Jobs

```php
<?php
// Schedule a job to run every minute
WebsiteSQL::cron()->schedule('* * * * *', function() {
    // This runs every minute
    echo "Running every minute\n";
});

// Schedule a job to run hourly at the top of the hour
WebsiteSQL::cron()->schedule('0 * * * *', function() {
    // This runs at 1:00, 2:00, 3:00, etc.
    echo "Running hourly\n";
});

// Schedule a job to run at 2:30am daily
WebsiteSQL::cron()->schedule('30 2 * * *', function() {
    // This runs at 2:30am
    echo "Running at 2:30am\n";
});

// Schedule a job to run on weekdays at noon
WebsiteSQL::cron()->schedule('0 12 * * 1-5', function() {
    // This runs at noon Monday-Friday
    echo "Running at noon on weekdays\n";
});
```

### Named Jobs

You can assign names to jobs, which allows you to reference them later:

```php
// Schedule a named job
WebsiteSQL::cron()->schedule('0 0 * * *', function() {
    // Daily backup at midnight
    // ...
}, 'daily-backup');

// Remove a scheduled job by name
WebsiteSQL::cron()->removeJob('daily-backup');
```

### Controller Methods as Jobs

You can use controller methods as job callbacks:

```php
// Schedule a controller method to run every 5 minutes
WebsiteSQL::cron()->schedule('*/5 * * * *', 'App\\Jobs\\ReportGenerator@generateDailyReport');
```

### Using the Built-in Runner

WebsiteSQL includes a built-in cron runner script that can be executed by your system's cron scheduler. To use it, set up a cron entry to run every minute:

```
# Linux/Unix crontab entry
* * * * * php /path/to/websitesql/framework/bin/cron-runner.php >> /path/to/cron.log 2>&1
```

For Windows, use Task Scheduler to run this PHP script every minute.

### Implementing a Custom Runner

If you prefer to implement your own cron runner, you can easily do so. Here's a simple example of a custom runner:

```php
<?php
// Custom cron runner - mycron.php

// Require autoloader
require __DIR__ . '/vendor/autoload.php';

// Include your application bootstrap if needed
require __DIR__ . '/bootstrap/app.php';

// Log the execution
echo "[" . date('Y-m-d H:i:s') . "] Cron runner started\n";

// Execute due cron jobs
$results = \WebsiteSQL\WebsiteSQL::cron()->run();

// Log the results
if (!empty($results)) {
    echo "Executed " . count($results) . " jobs:\n";
    foreach ($results as $name => $result) {
        echo "- Job '{$name}' executed\n";
    }
} else {
    echo "No jobs were due for execution.\n";
}
```

Then schedule this custom runner to run every minute using your system's cron or task scheduler:

```
# Linux/Unix crontab entry
* * * * * php /path/to/your-app/mycron.php >> /path/to/cron.log 2>&1
```

### Testing Scheduled Jobs

You can test your scheduled jobs without waiting for the actual time by simulating timestamps:

```php
// Schedule a job
WebsiteSQL::cron()->schedule('0 12 * * *', function() {
    return "Job executed!";
}, 'noon-job');

// Test the job with a specific timestamp
// This simulates the cron running at noon on May 11, 2023
$timestamp = strtotime('2023-05-11 12:00:00');
$results = WebsiteSQL::cron()->run($timestamp);

// $results will contain the return value of executed job(s)
print_r($results);
```

### Cron Expression Format

WebsiteSQL uses the standard cron expression format with five fields:

```
┌───────────── minute (0 - 59)
│ ┌───────────── hour (0 - 23)
│ │ ┌───────────── day of the month (1 - 31)
│ │ │ ┌───────────── month (1 - 12)
│ │ │ │ ┌───────────── day of the week (0 - 6) (Sunday to Saturday)
│ │ │ │ │
* * * * *
```

Supported expressions include:
- `*` - any value
- `1,3,5` - list of values
- `1-5` - range of values
- `*/5` - step values (every 5 minutes)
- `0-30/5` - step values within a range (every 5 minutes for the first half hour)

## Project Structure

```
my-project/
├── app/
│   ├── Controllers/
│   ├── Middleware/
│   └── Models/
├── config/
│   └── app.php
├── database/
│   └── migrations/
├── public/
│   └── index.php
├── routes/
│   └── web.php
└── vendor/
```

### Example index.php

```php
<?php

// Require the Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Load configuration
$config = require __DIR__ . '/../config/app.php';
WebsiteSQL\WebsiteSQL::config()->add($config);

// Set up database connection
WebsiteSQL\WebsiteSQL::db()->config([
    'host' => WebsiteSQL\WebsiteSQL::config()->get('db.host'),
    'database' => WebsiteSQL\WebsiteSQL::config()->get('db.database'),
    'user' => WebsiteSQL\WebsiteSQL::config()->get('db.user'),
    'password' => WebsiteSQL\WebsiteSQL::config()->get('db.password'),
]);

// Load routes
require __DIR__ . '/../routes/web.php';

// Start the application
WebsiteSQL\WebsiteSQL::start();
```

### Example routes.php

```php
<?php

use WebsiteSQL\WebsiteSQL;

// Define routes
WebsiteSQL::router()->get('/', 'HomeController@index');

WebsiteSQL::router()->get('/users', 'UserController@index');
WebsiteSQL::router()->get('/users/:id', 'UserController@show');
WebsiteSQL::router()->post('/users', 'UserController@store');

// API routes
WebsiteSQL::router()->group('/api', function($router) {
    $router->get('/users', function($req, $res) {
        $users = WebsiteSQL::db()->get('users');
        return $res->json(['users' => $users]);
    });
});

// Route with middleware
WebsiteSQL::router()->get('/dashboard', 'DashboardController@index')
    ->middleware(['auth']);
```

### Example Controller

```php
<?php
namespace App\Controllers;

use WebsiteSQL\WebsiteSQL;

class UserController {
    public function index($request, $response) {
        $users = WebsiteSQL::db()->get('users');
        
        // Return JSON response
        return $response->json(['users' => $users]);
    }
    
    public function show($request, $response, $id) {
        $user = WebsiteSQL::db()->get('users', '*', ['id' => $id]);
        
        if (!$user) {
            return $response->status(404)
                ->json(['error' => 'User not found']);
        }
        
        return $response->json(['user' => $user]);
    }
    
    public function store($request, $response) {
        // Get form or JSON data
        $data = $request->input();
        
        // Validate data
        if (empty($data['name']) || empty($data['email'])) {
            return $response->status(400)
                ->json(['error' => 'Name and email are required']);
        }
        
        $userId = WebsiteSQL::db()->insert('users', [
            'name' => $data['name'],
            'email' => $data['email']
        ]);
        
        return $response->status(201)
            ->json(['id' => $userId, 'message' => 'User created']);
    }
}
```

## License

The WebsiteSQL framework is open-source software licensed under the MIT license.

## Contributing

We welcome contributions to WebsiteSQL! Please feel free to submit a Pull Request.

## Security

If you discover any security related issues, please email security@websitesql.com instead of using the issue tracker.