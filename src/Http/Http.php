<?php declare(strict_types=1);

namespace WebsiteSQL\Http;

use WebsiteSQL\Http\Message\ResponseInterface;
use WebsiteSQL\Http\Message\ServerRequestInterface;
use WebsiteSQL\Http\Message\JsonResponse;
use WebsiteSQL\Http\Message\HtmlResponse;
use WebsiteSQL\Http\Message\TextResponse;
use WebsiteSQL\Http\Message\RedirectResponse;
use WebsiteSQL\Http\Factory\ResponseFactory;
use WebsiteSQL\Http\ServerRequest\ServerRequest;

class Http {
    /**
     * @var ResponseFactory
     */
    private $factory;
    
    public function __construct()
    {
        $this->factory = new ResponseFactory();
    }
    
    /**
     * Create a server request from global variables
     *
     * @return ServerRequestInterface
     */
    public function createServerRequestFromGlobals(): ServerRequestInterface
    {
        return ServerRequest::fromGlobals();
    }
    
    /**
     * Create a new response
     *
     * @param int $code HTTP status code
     * @return ResponseInterface
     */
    public function createResponse(int $code = 200): ResponseInterface
    {
        return $this->factory->createResponse($code);
    }
    
    /**
     * Create a JSON response
     *
     * @param mixed $data The data to encode as JSON
     * @param int $code HTTP status code
     * @return JsonResponse
     */
    public function jsonResponse($data, int $code = 200): JsonResponse
    {
        return $this->factory->createJsonResponse($data, $code);
    }
    
    /**
     * Create a text response
     *
     * @param string $text The text content
     * @param int $code HTTP status code
     * @return TextResponse
     */
    public function textResponse(string $text, int $code = 200): TextResponse
    {
        return $this->factory->createTextResponse($text, $code);
    }
    
    /**
     * Create an HTML response
     *
     * @param string $html The HTML content
     * @param int $code HTTP status code
     * @return HtmlResponse
     */
    public function htmlResponse(string $html, int $code = 200): HtmlResponse
    {
        return $this->factory->createHtmlResponse($html, $code);
    }
    
    /**
     * Create an empty response
     *
     * @param int $code HTTP status code
     * @return ResponseInterface
     */
    public function emptyResponse(int $code = 204): ResponseInterface
    {
        return $this->createResponse($code);
    }
    
    /**
     * Create a redirect response
     *
     * @param string $url The URL to redirect to
     * @param int $code HTTP status code
     * @return RedirectResponse
     */
    public function redirect(string $url, int $code = 302): RedirectResponse
    {
        return $this->factory->createRedirectResponse($url, $code);
    }
    
    /**
     * Emit a response to the client
     *
     * @param ResponseInterface $response
     * @return void
     */
    public function emitResponse(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());
        
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }
        
        echo $response->getBody();
    }
}