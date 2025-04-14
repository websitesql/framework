<?php

namespace WebsiteSQL\Framework\Middleware;

use Error;
use Exception;
use WebsiteSQL\Framework\App;
use League\Route\Http\Exception\UnauthorizedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class CommonMiddleware implements MiddlewareInterface
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
        // Get the token from the Authorization header if it exists
        $authorizationHeader = $request->getHeaderLine('Authorization');
        $authorizationToken = $this->app->getUtilities()->parseAuthorization($authorizationHeader);

        // Get the token from the Cookie header if it exists
        $parsedCookies = $this->app->getUtilities()->parseCookies($request->getHeaderLine('Cookie'));
        $cookiesToken = $parsedCookies['access_token'] ?? null;

        // Get the token
        $token = $authorizationToken ?? $cookiesToken;

        // Verify the token
        $confirmToken = $this->app->getAuth()->confirmToken($token ?? '');

        // Check if the check method returned a string
        if ($confirmToken) {
			// Renew the token
			$renew_token = $this->app->getAuth()->renewToken($token);

            // Add token to request
            $request = $request->withAttribute('token', $token);

            // Get user details from token
            $user = $this->app->getUser()->getUserById($this->app->getAuth()->getUserId($token));

            // Add user details to request
            $request = $request->withAttribute('user', $user);

            // Proceed with the request handling
            $response = $handler->handle($request);

			// Generate the cookie string
			$cookieString = $this->app->getUtilities()->generateCookieHeader('access_token', $renew_token['token'], [
				'domain' => $this->app->getConfig()->get('cookies.domain'),
				'expires' => $renew_token['expires_at'],
			]);

            // Add the Set-Cookie header to the response
            return $response->withAddedHeader('Set-Cookie', $cookieString);
        } else {
            // Continue processing the request
            return $handler->handle($request);
        }
    }
}