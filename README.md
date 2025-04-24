# WebsiteSQL Framework

A lightweight, flexible PHP framework with support for routing, database operations, and migrations.

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Routing](#routing)
- [Middleware](#middleware)
- [Database Operations](#database-operations)
- [Migrations](#migrations)
- [Project Structure](#project-structure)
- [License](#license)

## Installation

### Requirements
- PHP 7.2 or later
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
WebsiteSQL::route('GET', '/', function() {
    echo 'Hello, WebsiteSQL!';
})->name('home');

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

### Basic Routes

```php
// Simple route with closure
WebsiteSQL::route('GET', '/', function() {
    echo 'Hello World!';
});

// Route with controller
WebsiteSQL::route('GET', '/users', 'App\\Controllers\\UserController@index');

// Route with parameters
WebsiteSQL::route('GET', '/user/{id}', function($id) {
    echo "User ID: {$id}";
});

// Named routes
WebsiteSQL::route('GET', '/about', function() {
    echo 'About Us';
})->name('about');
```

### Route with Middleware

```php
// Apply middleware to a route
WebsiteSQL::route('GET', '/dashboard', 'DashboardController@index')
    ->middleware(['auth'])
    ->name('dashboard');
```

### HTTP Methods

WebsiteSQL supports all standard HTTP methods:

```php
WebsiteSQL::route('GET', '/users', 'UserController@index');
WebsiteSQL::route('POST', '/users', 'UserController@store');
WebsiteSQL::route('PUT', '/users/{id}', 'UserController@update');
WebsiteSQL::route('DELETE', '/users/{id}', 'UserController@destroy');
```

### Route Groups

For larger applications, it's recommended to organize routes in a separate file:

```php
// routes/web.php
<?php

use WebsiteSQL\WebsiteSQL;

WebsiteSQL::route('GET', '/', 'HomeController@index')->name('home');
WebsiteSQL::route('GET', '/about', 'HomeController@about')->name('about');

// Then in your index.php
require __DIR__ . '/routes/web.php';
```

## Middleware

Middleware provides a way to filter HTTP requests:

```php
// Define middleware
WebsiteSQL::middleware('auth', function() {
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login page
        header('Location: /login');
        exit;
    }
    return true;
});

// Using controller class for middleware
WebsiteSQL::middleware('admin', 'App\\Middleware\\AdminMiddleware@handle');
```

### Applying Middleware to Routes

```php
// Single middleware
WebsiteSQL::route('GET', '/dashboard', 'DashboardController@index')
    ->middleware(['auth']);

// Multiple middleware
WebsiteSQL::route('GET', '/admin/settings', 'AdminController@settings')
    ->middleware(['auth', 'admin']);
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
WebsiteSQL::start();
```

### Example Controller

```php
<?php
namespace App\Controllers;

use WebsiteSQL\WebsiteSQL;

class UserController {
    public function index() {
        $users = WebsiteSQL::db()->get('users');
        // Render view with users
    }
    
    public function show($id) {
        $user = WebsiteSQL::db()->get('users', '*', ['id' => $id]);
        // Render view with user
    }
    
    public function store() {
        $userId = WebsiteSQL::db()->insert('users', [
            'name' => $_POST['name'],
            'email' => $_POST['email']
        ]);
        // Redirect to user page
    }
}
```

## Advanced Features

### Custom Error Handling

```php
// Register error handler
set_error_handler(function($severity, $message, $file, $line) {
    if (WebsiteSQL::config()->get('app.debug')) {
        echo "Error: {$message} in {$file} on line {$line}");
    } else {
        // Log error and show friendly message
        error_log("Error: {$message} in {$file} on line {$line}");
        echo "An error occurred. Please try again later.";
    }
});
```

### Environment Configuration

Use .env files for environment-specific configuration:

```
# .env
DB_HOST=localhost
DB_NAME=my_database
DB_USER=root
DB_PASS=secret
APP_DEBUG=true
```

```php
// Load .env file
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// Configure app
WebsiteSQL::config()->add([
    'app' => [
        'debug' => getenv('APP_DEBUG') === 'true'
    ],
    'db' => [
        'host' => getenv('DB_HOST'),
        'database' => getenv('DB_NAME'),
        'user' => getenv('DB_USER'),
        'password' => getenv('DB_PASS')
    ]
]);
```

## License

The WebsiteSQL framework is open-source software licensed under the MIT license.

## Contributing

We welcome contributions to WebsiteSQL! Please feel free to submit a Pull Request.

## Security

If you discover any security related issues, please email security@websitesql.com instead of using the issue tracker.