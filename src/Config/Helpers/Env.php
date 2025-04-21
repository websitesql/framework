<?php declare(strict_types=1);

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable or returns a default value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
		$value = false;
		
		// Check if the environment variable is set in $_ENV
        if (isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }

		// Check if the environment variable is set in $_SERVER
		if ($value === false && isset($_SERVER[$key])) {
			$value = $_SERVER[$key];
		}

		// Check if the environment variable is set in the system environment variables
		if ($value === false) {
			$value = getenv($key);
		}

		// If the value is not found in $_ENV, return the default value
		if ($value === false) {
			return $default;
		}

        // Convert special strings to their appropriate values
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }

        return $value;
    }
}