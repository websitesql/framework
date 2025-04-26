<?php declare(strict_types=1);

namespace WebsiteSQL\Http\ServerRequest;

use WebsiteSQL\Http\Message\RequestInterface;
use WebsiteSQL\Http\Message\ServerRequestInterface;
use WebsiteSQL\Http\Message\StreamInterface;
use WebsiteSQL\Http\Message\Stream;
use WebsiteSQL\Http\Message\UriInterface;
use WebsiteSQL\Http\Message\Uri;

class ServerRequest implements ServerRequestInterface
{
    private $method;
    private $uri;
    private $headers = [];
    private $body;
    private $protocolVersion = '1.1';
    private $serverParams = [];
    private $cookieParams = [];
    private $queryParams = [];
    private $uploadedFiles = [];
    private $parsedBody;
    private $attributes = [];
    private $requestTarget;

    public function __construct(
        $method, 
        $uri, 
        array $headers = [], 
        StreamInterface $body = null, 
        $version = '1.1'
    ) {
        $this->method = $method;
        $this->uri = $uri instanceof UriInterface ? $uri : new Uri($uri);
        $this->headers = $headers;
        $this->body = $body ?: new Stream(fopen('php://temp', 'r+'));
        $this->protocolVersion = $version;
    }

    public static function fromGlobals()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Build the URI
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        $port = $_SERVER['SERVER_PORT'] ?? null;
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Strip the query string from the path
        $queryPos = strpos($path, '?');
        if ($queryPos !== false) {
            $path = substr($path, 0, $queryPos);
        }
        
        $query = $_SERVER['QUERY_STRING'] ?? '';
        
        // Build the full URI
        $uri = new Uri();
        $uri = $uri->withScheme($scheme)
                   ->withHost($host)
                   ->withPath($path)
                   ->withQuery($query);
        
        // Add port if not standard
        if ($port !== null) {
            $standardPorts = ['http' => 80, 'https' => 443];
            if (!isset($standardPorts[$scheme]) || $port != $standardPorts[$scheme]) {
                $uri = $uri->withPort((int)$port);
            }
        }
        
        // Get headers
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        
        $serverRequest = new self($method, $uri, $headers);
        
        $serverRequest->serverParams = $_SERVER;
        $serverRequest->cookieParams = $_COOKIE;
        $serverRequest->queryParams = $_GET;
        $serverRequest->uploadedFiles = $_FILES;
        $serverRequest->parsedBody = $_POST;
        
        return $serverRequest;
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;
        return $clone;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->parsedBody = $data;
        return $clone;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute($name, $value): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    public function withoutAttribute($name): ServerRequestInterface
    {
        $clone = clone $this;
        if (isset($clone->attributes[$name])) {
            unset($clone->attributes[$name]);
        }
        return $clone;
    }

    public function getRequestTarget(): string
    {
        if (isset($this->requestTarget)) {
            return $this->requestTarget;
        }
        
        $target = $this->uri->getPath();
        if ($target === '') {
            $target = '/';
        }
        
        $query = $this->uri->getQuery();
        if ($query !== '') {
            $target .= '?' . $query;
        }
        
        return $target;
    }
    public function withRequestTarget($requestTarget): RequestInterface
    {
        $clone = clone $this;
        $clone->requestTarget = $requestTarget;
        return $clone;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod($method): RequestInterface
    {
        $clone = clone $this;
        $clone->method = $method;
        return $clone;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri($uri, $preserveHost = false): RequestInterface
    {
        if (!$uri instanceof UriInterface) {
            $uri = new Uri($uri);
        }
        
        $new = clone $this;
        $new->uri = $uri;
        
        if (!$preserveHost || !$this->hasHeader('Host')) {
            $host = $uri->getHost();
            if ($host !== '') {
                $port = $uri->getPort();
                if ($port !== null) {
                    $host .= ':' . $port;
                }
                
                $new = $new->withHeader('Host', $host);
            }
        }
        
        return $new;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version): RequestInterface
    {
        $clone = clone $this;
        $clone->protocolVersion = $version;
        return $clone;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function getHeader($name): array
    {
        $name = strtolower($name);
        return $this->headers[$name] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader($name, $value): RequestInterface
    {
        $clone = clone $this;
        $name = strtolower($name);
        $clone->headers[$name] = is_array($value) ? $value : [$value];
        return $clone;
    }

    public function withAddedHeader($name, $value): RequestInterface
    {
        $clone = clone $this;
        $name = strtolower($name);
        if (!isset($clone->headers[$name])) {
            $clone->headers[$name] = [];
        }
        $valueArray = is_array($value) ? $value : [$value];
        $clone->headers[$name] = array_merge($clone->headers[$name], $valueArray);
        return $clone;
    }

    public function withoutHeader($name): RequestInterface
    {
        $clone = clone $this;
        $name = strtolower($name);
        unset($clone->headers[$name]);
        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody($body): RequestInterface
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }
}
