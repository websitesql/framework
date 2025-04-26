<?php declare(strict_types=1);

namespace WebsiteSQL\Utilities;

use Exception;
use DateTime;

class Utilities
{
    /*
     * This method returns a random string of a specified length
     * 
     * @return string
     */
    public function randomString(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /*
     * This method returns a random number of a specified length
     * 
     * @return int
     */
    public function randomNumber(int $length = 10): int
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomNumber = '';

        for ($i = 0; $i < $length; $i++) {
            $randomNumber .= $characters[rand(0, $charactersLength - 1)];
        }

        return (int) $randomNumber;
    }
    
    /*
     * This method returns a slugified string
     * 
     * @return string
     */
    public function slugify(string $string): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    }

    /*
     * This method returns a time ago string
     * 
     * @return string
     */
    public function timeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        $now = time();
        $ago = $now - $time;

        if ($ago < 60) {
            return 'just now';
        } elseif ($ago < 3600) {
            return round($ago / 60) . ' minutes ago';
        } elseif ($ago < 86400) {
            return round($ago / 3600) . ' hours ago';
        } elseif ($ago < 604800) {
            return round($ago / 86400) . ' days ago';
        } elseif ($ago < 2592000) {
            return round($ago / 604800) . ' weeks ago';
        } elseif ($ago < 31536000) {
            return round($ago / 2592000) . ' months ago';
        } else {
            return round($ago / 31536000) . ' years ago';
        }
    }

    /*
     * This method gets the current date and time
     * 
     * @param string $date
     * @return string
     */
    public function getDateTime(string $date = null): string
    {
        if (!$date) {
            return date('Y-m-d H:i:s');
        } else {
            return date('Y-m-d H:i:s', strtotime($date));
        }
    }

    /*
     * This method generates a uuid
     * 
     * @param string $version
     * @return string
     */
    public function generateUuid($version = 4) {
        // Generate 16 random bytes.
        $data = openssl_random_pseudo_bytes(16);
    
        switch ($version) {
            case 1:
                // Set the version to 0001 (version 1) and adjust the variant.
                $data[6] = chr(ord($data[6]) & 0x0f | 0x10); // Set version to 0001
                $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Set variant to 10xx
                break;
            case 3:
                // Set the version to 0011 (version 3) and adjust the variant.
                $data[6] = chr(ord($data[6]) & 0x0f | 0x30); // Set version to 0011
                $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Set variant to 10xx
                break;
            case 4:
                // Set the version to 0100 (version 4) and adjust the variant.
                $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Set version to 0100
                $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Set variant to 10xx
                break;
            case 5:
                // Set the version to 0101 (version 5) and adjust the variant.
                $data[6] = chr(ord($data[6]) & 0x0f | 0x50); // Set version to 0101
                $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Set variant to 10xx
                break;
            default:
                throw new Exception("Unsupported UUID version: $version");
        }
    
        // Convert the random bytes to a UUID string format.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /*
     * This method handles search and pagination
     * 
     * @param array $data
     * @param int $page
     * @param int $limit
     * @param string $search
     * @param array $searchColumns
     * @return array
     */
    public function searchAndPaginate(array $data, int $offset = null, int $limit = null, string $search = null, array $searchColumns = []): array
    {
        // Create a response array
        $response = [];

        // Prepare the data
        $filteredData = $data;

        // If search term is provided, filter the data
        if ($search) {
            // Filter the data
            $filteredData = array_filter($filteredData, function ($item) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    if (stripos($item[$column], $search) !== false) {
                        return true;
                    }
                }
                return false;
            });

            // Add the search term to the response
            $response['search'] = $search;
        }

        // If offset is provided, slice the data
        if ($offset !== null) {
            // If offset is greater than the data length, return an empty array
            if ($offset > count($filteredData)) {
                return [];
            }

            // Slice the data
            $filteredData = array_slice($filteredData, $offset);

            // Add the offset to the response
            $response['offset'] = $offset;
        }

        // If limit is provided, slice the data
        if ($limit !== null) {
            // Slice the data
            $filteredData = array_slice($filteredData, 0, $limit);

            // Add the limit to the response
            $response['limit'] = $limit;
        }

        // Add the total number of items to the response
        $response['total'] = count($data);

        // Add the data to the response
        $response['data'] = $filteredData;

        return $response;
    }

    /*
     * This method validates a date
     * 
     * @param string $date
     * @param bool $strict (optional)
     * @return bool
     */
    public function validateDate($date, $strict = true)
    {
        $dateTime = DateTime::createFromFormat('m/d/Y', $date);
        if ($strict) {
            $errors = DateTime::getLastErrors();
            if (!empty($errors['warning_count'])) {
                return false;
            }
        }
        return $dateTime !== false;
    }

    /*
     * This method gets a token from the Cookie header
     * 
     * @param string $cookieHeader
     * @return string|null
     */
    public function parseCookies($cookieHeader) {
        $cookies = explode(';', $cookieHeader);
        $output = [];

        foreach ($cookies as $cookie) {
            $cookieParts = explode('=', trim($cookie), 2);
            if (count($cookieParts) === 2) {
                list($name, $value) = $cookieParts;
                $output[$name] = $value;
            }
        }

        return $output;
    }

    /*
     * This method parses the Authorization header
     * 
     * @param string $authorizationHeader
     * @return string|null
     */
    public function parseAuthorization($authorizationHeader) 
    {
        $token = null;
        if (!empty($authorizationHeader)) {
            $token = substr($authorizationHeader, 7); // Remove "Bearer "
        }
        return $token;
    }

    /*
     * This method takes the created_at date for the token and calculates the expiry date from the max age and refresh age
     * 
     * @param DateTime $createdAt
     * @param int $maxAge
     * @param int $refreshAge
     * @return DateTime
     */
    public function calculateExpiryDate(DateTime $createdAt, int $maxAge, int $refreshAge): DateTime
    {
        // Get the times
        $currentTime = new DateTime();

        // Calculate the maxmium age of the token
        $expiryDate = clone $createdAt;
        $expiryDate->modify('+' . $maxAge . ' seconds');

        // If the expiry date is less than the current time throw an error
        if ($expiryDate < $currentTime) {
            throw new Exception('Token has expired');
        }

        // Calculate the refresh age of the token
        $refreshDate = clone $createdAt;
        $refreshDate->modify('+' . $refreshAge . ' seconds');

        // If the refresh date is less the maximum age, set the refresh date to the maximum age
        if ($refreshDate < $expiryDate) {
            $refreshDate = clone $expiryDate;
        }

        return $refreshDate;
    }

    /*
     * This method generates the cookie header string from the access_token, host and expiry date
     * 
     * @param string $access_token
     * @param string $domain
	 * @param array $options (optional) [domain, path, expires, httpOnly, sameSite, secure]
     * @return string
     */
    public function generateCookieHeader($name, $value, $options = [])
    {
		// Get the options or set the defaults
		$domain = $options['domain'] ?? null;
		$path = $options['path'] ?? '/';
		$expires = $options['expires'] ?? new DateTime('+1 hour');
		$httpOnly = $options['httpOnly'] ?? true;
		$sameSite = $options['sameSite'] ?? 'Strict';
		$secure = $options['secure'] ?? true;

        // Create the cookie string
        $cookieString = sprintf('%s=%s; ', $name, $value);

        // Add the domain to the cookie string
        if (!empty($domain)) {
            $cookieString .= sprintf('Domain=%s; ', $domain);
        }

		// Add the path
		$cookieString .= sprintf('Path=%s; ', $path);

		// Add the expiry date
		$cookieString .= sprintf('Expires=%s; ', $expires->format(DateTime::COOKIE));

		// Add the HttpOnly flag
		if ($httpOnly) {
			$cookieString .= 'HttpOnly; ';
		}

		// Add the SameSite flag
		if (!empty($sameSite)) {
			$cookieString .= sprintf('SameSite=%s; ', $sameSite);
		}

		// Add the Secure flag
		if ($secure) {
			$cookieString .= 'Secure; ';
		}

        return $cookieString;
    }
}