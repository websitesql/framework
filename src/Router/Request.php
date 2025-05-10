<?php declare(strict_types=1);

namespace WebsiteSQL\Router;

class Request {
    /**
     * Request headers
     *
     * @var array
     */
    protected $headers = [];
    
    /**
     * Request body parameters
     *
     * @var array
     */
    protected $data = [];
    
    /**
     * Query string parameters
     *
     * @var array
     */
    protected $query = [];
    
    /**
     * Files uploaded in the request
     *
     * @var array
     */
    protected $files = [];
    
    /**
     * Create a new Request instance from global variables
     *
     * @return \WebsiteSQL\Http\Request
     */
    public static function createFromGlobals() {
        $instance = new self();
        $instance->headers = self::getHeaders();
        $instance->query = $_GET;
        $instance->files = $_FILES;
        
        // Check if the request is JSON
        if (isset($_SERVER['CONTENT_TYPE']) && 
            strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $body = file_get_contents('php://input');
            $instance->data = json_decode($body, true) ?: [];
        } else {
            $instance->data = $_POST;
        }
        
        return $instance;
    }
    
    /**
     * Get all headers from the current request
     * 
     * @return array The headers
     */
    private static function getHeaders() {
        $headers = [];
        
        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                $headers[strtolower($name)] = $value;
            }
            return $headers;
        }
        
        // Fallback if getallheaders() is not available
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[strtolower(str_replace(' ', '-', 
                    ucwords(strtolower(str_replace('_', ' ', 
                        substr($name, 5))))))] = $value;
            }
        }
        
        return $headers;
    }

    /**
     * Get the HTTP method
     *
     * @return string
     */
    public function method() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Check if the request method is GET
     *
     * @return bool
     */
    public function isGet() {
        return $this->method() === 'GET';
    }
    
    /**
     * Check if the request method is POST
     *
     * @return bool
     */
    public function isPost() {
        return $this->method() === 'POST';
    }
    
    /**
     * Check if the request method is PUT
     *
     * @return bool
     */
    public function isPut() {
        return $this->method() === 'PUT';
    }
    
    /**
     * Check if the request method is DELETE
     *
     * @return bool
     */
    public function isDelete() {
        return $this->method() === 'DELETE';
    }
    
    /**
     * Check if the request method is AJAX
     *
     * @return bool
     */
    public function isAjax() {
        return isset($this->headers['x-requested-with']) && 
            strtolower($this->headers['x-requested-with']) === 'xmlhttprequest';
    }
    
    /**
     * Get the request URI
     *
     * @return string
     */
    public function uri() {
        return $_SERVER['REQUEST_URI'];
    }
    
    /**
     * Get the request path
     *
     * @return string
     */
    public function path() {
        $uri = $this->uri();
        $path = parse_url($uri, PHP_URL_PATH);
        return '/' . trim($path, '/');
    }
    
    /**
     * Get a header value
     *
     * @param string $name Header name
     * @param mixed $default Default value if header not found
     * @return mixed
     */
    public function header($name, $default = null) {
        $name = strtolower($name);
        return $this->headers[$name] ?? $default;
    }
    
    /**
     * Get all headers
     *
     * @return array
     */
    public function headers() {
        return $this->headers;
    }
    
    /**
     * Get a query parameter
     *
     * @param string $key Parameter name
     * @param mixed $default Default value if parameter not found
     * @return mixed
     */
    public function query($key = null, $default = null) {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }
    
    /**
     * Get a request body parameter
     *
     * @param string $key Parameter name
     * @param mixed $default Default value if parameter not found
     * @return mixed
     */
    public function input($key = null, $default = null) {
        if ($key === null) {
            return $this->data;
        }
        return $this->data[$key] ?? $default;
    }
    
    /**
     * Get an uploaded file
     *
     * @param string $key File name
     * @return array|null
     */
    public function file($key) {
        return $this->files[$key] ?? null;
    }
    
    /**
     * Get all files
     *
     * @return array
     */
    public function files() {
        return $this->files;
    }
    
    /**
     * Get client IP address
     *
     * @return string
     */
    public function ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    /**
     * Get user agent
     *
     * @return string
     */
    public function userAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
    
    /**
     * Check if the request is secure (HTTPS)
     *
     * @return bool
     */
    public function isSecure() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    }
}