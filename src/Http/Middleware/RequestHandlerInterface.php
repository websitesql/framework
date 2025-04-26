<?php declare(strict_types=1);

namespace WebsiteSQL\Http\Middleware;

use WebsiteSQL\Http\Message\ResponseInterface;
use WebsiteSQL\Http\Message\ServerRequestInterface;

/**
 * Handles a server request and produces a response.
 */
interface RequestHandlerInterface
{
    /**
     * Handles a request and produces a response.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
