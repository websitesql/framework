## Running the console application

The console application is run by calling it from the command line. The entry point is the `console.php` file located in the root directory of the project. You can run it like this:

```bash
php {filename} [options] [arguments]
```

Where `{filename}` is the path to the `console.php` file. For example, if you are in the root directory of the project, you can run:

```php
#!/usr/bin/env php
<?php

// Require autoload
require_once __DIR__ . '/vendor/autoload.php';

// Run the console application
new WebsiteSQL\Framework\Core\Console($argv);
```