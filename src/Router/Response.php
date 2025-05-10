<?php declare(strict_types=1);

namespace WebsiteSQL\Router;

class Response {
    /**
     * Response body
     *
     * @var string
     */
    protected $body = '';
    
    /**
     * Response status code
     *
     * @var int
     */
    protected $status = 200;
    
    /**
     * Response headers
     *
     * @var array
     */
    protected $headers = [];
    
    /**
     * Set the response body
     *
     * @param string $content Response content
     * @return $this
     */
    public function body($content) {
        $this->body = $content;
        return $this;
    }
    
    /**
     * Set the response status code
     *
     * @param int $code HTTP status code
     * @return $this
     */
    public function status($code) {
        $this->status = $code;
        return $this;
    }
    
    /**
     * Set a response header
     *
     * @param string $name Header name
     * @param string $value Header value
     * @return $this
     */
    public function header($name, $value) {
        $this->headers[$name] = $value;
        return $this;
    }
    
    /**
     * Set response content type
     *
     * @param string $type Content type
     * @return $this
     */
    public function type($type) {
        return $this->header('Content-Type', $type);
    }
    
    /**
     * Set response as JSON
     *
     * @param mixed $data Data to encode as JSON
     * @return $this
     */
    public function json($data) {
        $this->type('application/json');
        $this->body(json_encode($data));
        return $this;
    }
    
    /**
     * Set response as HTML
     *
     * @param string $html HTML content
     * @return $this
     */
    public function html($html) {
        $this->type('text/html; charset=UTF-8');
        $this->body($html);
        return $this;
    }
    
    /**
     * Set response as plain text
     *
     * @param string $text Text content
     * @return $this
     */
    public function text($text) {
        $this->type('text/plain; charset=UTF-8');
        $this->body($text);
        return $this;
    }
    
    /**
     * Redirect to a URL
     *
     * @param string $url URL to redirect to
     * @param int $code HTTP status code (default: 302)
     * @return $this
     */
    public function redirect($url, $code = 302) {
        $this->status($code);
        $this->header('Location', $url);
        return $this;
    }
    
    /**
     * Set a cookie
     *
     * @param string $name Cookie name
     * @param string $value Cookie value
     * @param int $expire Expiration time
     * @param string $path Cookie path
     * @param string $domain Cookie domain
     * @param bool $secure HTTPS only
     * @param bool $httpOnly HTTP only (not accessible via JavaScript)
     * @return $this
     */
    public function cookie($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httpOnly = false) {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
        return $this;
    }
    
    /**
     * Set a raw cookie header
     *
     * @param string $header Raw cookie header
     * @return $this
     */
    public function rawCookie($header) {
        header('Set-Cookie: ' . $header);
        return $this;
    }
    
    /**
     * Generate a file download response
     *
     * @param string $path File path
     * @param string $name File name for the browser
     * @param string $type MIME type
     * @return $this
     */
    public function download($path, $name = null, $type = null) {
        if (!is_readable($path)) {
            throw new \RuntimeException("File not found or not readable: $path");
        }
        
        $name = $name ?: basename($path);
        $type = $type ?: (function_exists('mime_content_type') ? mime_content_type($path) : 'application/octet-stream');
        $size = filesize($path);
        
        $this->type($type);
        $this->header('Content-Disposition', 'attachment; filename="' . $name . '"');
        $this->header('Content-Length', (string) $size);
        $this->body(file_get_contents($path));
        
        return $this;
    }
    
    /**
     * Output a file for inline display in the browser
     *
     * @param string $path File path
     * @param string $type MIME type
     * @return $this
     */
    public function file($path, $type = null) {
        if (!is_readable($path)) {
            throw new \RuntimeException("File not found or not readable: $path");
        }
        
        $type = $type ?: (function_exists('mime_content_type') ? mime_content_type($path) : 'application/octet-stream');
        $size = filesize($path);
        
        $this->type($type);
        $this->header('Content-Length', (string) $size);
        $this->body(file_get_contents($path));
        
        return $this;
    }
    
    /**
     * Render a view
     *
     * @param string $view Path to the view file
     * @param array $data Data to pass to the view
     * @return $this
     */
    public function render($view, $data = []) {
        if (!is_readable($view)) {
            throw new \RuntimeException("View not found or not readable: $view");
        }
        
        // Extract data to make variables available in the view
        extract($data);
        
        // Capture the view output
        ob_start();
        include $view;
        $content = ob_get_clean();
        
        $this->type('text/html; charset=UTF-8');
        $this->body($content);
        
        return $this;
    }
    
    /**
     * Send the response to the client
     *
     * @return void
     */
    public function send() {
        // Set status code
        http_response_code($this->status);
        
        // Send headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value", true);
        }
        
        // Send body
        echo $this->body;
        
        return $this;
    }
}