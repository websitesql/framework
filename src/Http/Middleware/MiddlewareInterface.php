<?php declare(strict_types=1);

namespace WebsiteSQL\Http\Middleware;

use WebsiteSQL\Http\Message\ResponseInterface;
use WebsiteSQL\Http\Message\ServerRequestInterface;

/**
 * Participant in processing a server request and response.
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;
}
