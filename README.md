# WebsiteSQL Framework

A lightweight, flexible PHP framework with a simple and intuitive API inspired by Flight PHP.

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Routing](#routing)
- [Middleware](#middleware)
- [Working with Requests & Responses](#working-with-requests--responses)
- [Database Operations](#database-operations)
- [Migrations](#migrations)
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

Middleware provides a convenient way to filter HTTP requests:

```php
// Define middleware with a closure
WebsiteSQL::router()->before(function($request, $response) {
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login page
        return $response->redirect('/login');
    }
});

// Using a class for middleware
WebsiteSQL::router()->before('App\\Middleware\\AuthMiddleware@handle');
```

### Applying Middleware to Specific Routes

```php
// Single middleware
WebsiteSQL::router()->get('/dashboard', function($request, $response) {
    return $response->html('Dashboard');
})->middleware('auth');

// Multiple middleware
WebsiteSQL::router()->get('/admin/settings', function($request, $response) {
    return $response->html('Admin Settings');
})->middleware(['auth', 'admin']);
```

### After Middleware

```php
// Executed after the route handler
WebsiteSQL::router()->after(function($request, $response) {
    // Log the request
    logRequest($request->method(), $request->path());
    return $response;
});
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