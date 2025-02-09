<?php declare(strict_types=1);

namespace AlanTiller\Framework\Strategy;

use JsonSerializable;
use League\Route\Route;
use League\Route\{ContainerAwareInterface, ContainerAwareTrait};
use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use League\Route\Strategy\AbstractStrategy;
use League\Route\Strategy\OptionsHandlerInterface;
use AlanTiller\Framework\Exceptions\{MethodNotAllowedException, NotFoundException, GeneralException};
use Error;
use Throwable;

class ApiStrategy extends AbstractStrategy implements ContainerAwareInterface, OptionsHandlerInterface
{
    use ContainerAwareTrait;

    /**
     * @var bool Whether debug mode is enabled.
     */
    protected bool $debug;

    /**
     * @var array|null CORS configuration
     */
    protected ?array $corsConfig = null;

    /**
     * Constructor.
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param int                      $jsonFlags
     * @param bool                     $debug            Whether debug mode is on
     */
    public function __construct(protected ResponseFactoryInterface $responseFactory, protected int $jsonFlags = 0, bool $debug = false) {
        $this->debug = $debug;
        $this->addResponseDecorator(static function (ResponseInterface $response): ResponseInterface {
            if (false === $response->hasHeader('content-type')) {
                $response = $response->withHeader('content-type', 'application/json');
            }
            return $response;
        });
    }

    /**
     * Configure CORS settings
     *
     * @param array $config
     * @return self
     */
    public function corsConfig(array $config): self
    {
        $this->corsConfig = $config;
        return $this;
    }

    /**
     * Add CORS headers to response
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function addCorsHeaders(ResponseInterface $response): ResponseInterface
    {
        if (!$this->corsConfig) {
            return $response;
        }

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowedOrigins = $this->corsConfig['allowedOrigins'] ?? [];
        $allowedMethods = $this->corsConfig['allowedMethods'] ?? ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
        $allowedHeaders = $this->corsConfig['allowedHeaders'] ?? ['Content-Type', 'Authorization', 'Accept'];
        $exposedHeaders = $this->corsConfig['exposedHeaders'] ?? [];
        $maxAge = $this->corsConfig['maxAge'] ?? 86400;
        $allowCredentials = $this->corsConfig['allowCredentials'] ?? false;

        // If no allowed origins are set, or origin doesn't match, return response without CORS headers
        if (empty($allowedOrigins) || (!in_array('*', $allowedOrigins) && !in_array($origin, $allowedOrigins))) {
            return $response;
        }

        // Set allowed origin
        $response = $response->withHeader(
            'Access-Control-Allow-Origin',
            in_array('*', $allowedOrigins) ? '*' : $origin
        );

        // Set other CORS headers
        $response = $response
            ->withHeader(
                'Access-Control-Allow-Methods',
                implode(', ', $allowedMethods)
            )
            ->withHeader(
                'Access-Control-Allow-Headers',
                implode(', ', $allowedHeaders)
            )
            ->withHeader(
                'Access-Control-Max-Age',
                (string)$maxAge
            );

        if (!empty($exposedHeaders)) {
            $response = $response->withHeader(
                'Access-Control-Expose-Headers',
                implode(', ', $exposedHeaders)
            );
        }

        if ($allowCredentials) {
            $response = $response->withHeader(
                'Access-Control-Allow-Credentials',
                'true'
            );
        }

        return $response;
    }

    /**
     * Returns the middleware for Method Not Allowed errors.
     */
    public function getMethodNotAllowedDecorator(\League\Route\Http\Exception\MethodNotAllowedException $exception): MiddlewareInterface
    {
        $methodNotAllowedException = new MethodNotAllowedException();
        return $this->buildJsonResponseMiddleware($methodNotAllowedException);
    }

    /**
     * Returns the middleware for Not Found errors.
     */
    public function getNotFoundDecorator(\League\Route\Http\Exception\NotFoundException $exception): MiddlewareInterface
    {
        $notFoundException = new NotFoundException();
        return $this->buildJsonResponseMiddleware($notFoundException);
    }

    /**
     * Returns the callable for HTTP OPTIONS requests.
     */
    public function getOptionsCallable(array $methods): callable
    {
        return function () use ($methods): ResponseInterface {
            $response = $this->responseFactory->createResponse(204);
            $response = $response->withHeader(
                'Allow',
                implode(', ', $methods)
            );
            
            return $this->addCorsHeaders($response);
        };
    }

    /**
     * Returns the middleware for handling any Throwable.
     */
    public function getThrowableHandler(): MiddlewareInterface
    {
        return new class ($this->responseFactory->createResponse(), $this) implements MiddlewareInterface
        {
            protected ResponseInterface $response;
            protected ApiStrategy $strategy;

            public function __construct(ResponseInterface $response, ApiStrategy $strategy)
            {
                $this->response = $response;
                $this->strategy = $strategy;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                try {
                    return $handler->handle($request);
                } catch (Throwable $exception) {
                    return $this->strategy->createErrorResponse($this->response, $exception);
                }
            }
        };
    }

    /**
     * Invokes the route callable and serializes JSON responses if needed.
     */
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $controller = $route->getCallable($this->getContainer());
        $result     = $controller($request, $route->getVars());

        if ($this->isJsonSerializable($result)) {
            $body     = json_encode($result, $this->jsonFlags);
            $result   = $this->responseFactory->createResponse();
            $result->getBody()->write($body);
        }

        return $this->addCorsHeaders($this->decorateResponse($result));
    }

    /**
     * Builds a JSON response middleware for a given Throwable.
     */
    protected function buildJsonResponseMiddleware(Throwable $exception): MiddlewareInterface
    {
        return new class ($this->responseFactory->createResponse(), $exception, $this) implements MiddlewareInterface
        {
            protected ResponseInterface $response;
            protected Throwable $exception;
            protected ApiStrategy $strategy;

            public function __construct(ResponseInterface $response, Throwable $exception, ApiStrategy $strategy)
            {
                $this->response  = $response;
                $this->exception = $exception;
                $this->strategy  = $strategy;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $this->strategy->createErrorResponse($this->response, $this->exception);
            }
        };
    }

    /**
     * Checks whether the given response is JSON-serializable.
     *
     * @param mixed $response
     * @return bool
     */
    protected function isJsonSerializable($response): bool
    {
        if ($response instanceof ResponseInterface) {
            return false;
        }
        return (is_array($response) || is_object($response) || $response instanceof JsonSerializable);
    }

    /**
     * Creates a standardized JSON error response.
     *
     * The response structure is:
     * {
     *   "error": {
     *       "message": "", // The Exception's message or "Unknown failure" if it's a plain Exception and debug is off
     *       "type": "",    // The Exception class name
     *       "code": 0,     // The HTTP Response code provided in the Exception
     *       "debug": []    // (optional) The exception trace if debug is on
     *   }
     * }
     *
     * @param ResponseInterface $response
     * @param Throwable         $exception
     * @return ResponseInterface
     */
    public function createErrorResponse(ResponseInterface $response, Throwable $exception): ResponseInterface {
        if ($this->debug) {
            // In debug mode, wrap non-API exceptions in GeneralException
            if (!$exception instanceof \AlanTiller\Framework\Exceptions\GeneralException) {
                $httpCode = $this->validateHttpCode($exception->getCode());
                $exception = new GeneralException(
                    $exception->getMessage(),
                    $httpCode, // Ensure we pass an integer
                    $exception
                );
            }
            
            $message = $exception->getMessage();
            $exceptionClass = get_class($exception);
            $httpCode = $this->validateHttpCode($exception->getCode());
            
            $error = [
                'error' => [
                    'message' => $message,
                    'type' => $exceptionClass,
                    'code' => $httpCode,
                    'debug' => [
                        'trace' => $exception->getTrace(),
                        'previous' => $exception->getPrevious() ? [
                            'message' => $exception->getPrevious()->getMessage(),
                            'type' => get_class($exception->getPrevious()),
                            'trace' => $exception->getPrevious()->getTrace(),
                        ] : null,
                    ]
                ]
            ];
        } else {
            // Production mode - validate and sanitize the error response
            if ($exception instanceof \AlanTiller\Framework\Exceptions\GeneralException) {
                $message = $exception->getMessage();
                $exceptionClass = get_class($exception);
                $httpCode = $this->validateHttpCode($exception->getCode());
            } else {
                // For non-Brou exceptions, throw a generic error
                $exception = new GeneralException();
                $message = $exception->getMessage();
                $exceptionClass = get_class($exception);
                $httpCode = $this->validateHttpCode($exception->getCode());
            }

            $error = [
                'error' => [
                    'message' => $message,
                    'type' => $exceptionClass,
                    'code' => $httpCode
                ]
            ];
        }

        $body = json_encode($error, $this->jsonFlags);
        $response->getBody()->write($body);

        $response = $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($httpCode, strtok($message, "\n"));

        return $this->addCorsHeaders($response);
    }

    /**
     * Validates and normalizes HTTP status codes.
     *
     * @param mixed $code
     * @return int
     */
    protected function validateHttpCode(mixed $code): int
    {
        // Handle string codes
        if (is_string($code)) {
            // Try to convert numeric strings to integers
            if (ctype_digit($code)) {
                $code = (int)$code;
            } else {
                return 500;
            }
        }
        
        // Handle non-integer codes
        if (!is_int($code)) {
            return 500;
        }

        // Validate the range
        if ($code < 100 || $code > 599) {
            return 500;
        }

        return $code;
    }
}