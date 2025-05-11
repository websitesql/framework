<?php
/**
 * WebsiteSQL Cron Runner
 * 
 * This script should be executed every minute by a system cron job:
 * * * * * * php /path/to/websitesql/framework/bin/cron-runner.php >> /dev/null 2>&1
 */

// Find the autoloader
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/../../../../vendor/autoload.php',
];

foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require $path;
        break;
    }
}

if (!class_exists('\\WebsiteSQL\\WebsiteSQL')) {
    die('Failed to load WebsiteSQL framework.');
}

// Load application bootstrap if available
$bootstrapPaths = [
    __DIR__ . '/../bootstrap/app.php',
    __DIR__ . '/../../bootstrap/app.php',
    __DIR__ . '/../../../bootstrap/app.php',
];

foreach ($bootstrapPaths as $path) {
    if (file_exists($path)) {
        require $path;
        break;
    }
}

// Execute the cron jobs
$results = \WebsiteSQL\WebsiteSQL::cron()->run();

// Output for logging purposes
if (!empty($results)) {
    echo '[' . date('Y-m-d H:i:s') . '] Cron jobs executed: ' . count($results) . PHP_EOL;
    
    foreach ($results as $name => $result) {
        echo " - Job '{$name}' executed" . PHP_EOL;
    }
} else {
    echo '[' . date('Y-m-d H:i:s') . '] No cron jobs due.' . PHP_EOL;
}