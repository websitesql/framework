<?php

namespace WebsiteSQL\Framework\Auth\Middleware;

use Error;
use WebsiteSQL\Framework\Core\App;
use WebsiteSQL\Framework\Exceptions\UnauthorizedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{
    /*
     * This object holds the database connection
     * 
     * @var Medoo
     */
    private App $app;

    /*
     * Constructor
     * 
     * @param Medoo $database
     */
    public function __construct(App $app) {
        $this->app = $app;
    }

    /*
     * This method processes the request
     * 
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * 
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        // Check if the user is logged in
        if (!$request->getAttribute('user')) {
            // Redirect to login if user is not logged in
            throw new UnauthorizedException();
        }

        // Continue processing the request
        return $handler->handle($request);
    }
}