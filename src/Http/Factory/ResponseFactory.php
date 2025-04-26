<?php declare(strict_types=1);

namespace WebsiteSQL\Http\Factory;

use WebsiteSQL\Http\Message\Response;
use WebsiteSQL\Http\Message\JsonResponse;
use WebsiteSQL\Http\Message\HtmlResponse;
use WebsiteSQL\Http\Message\TextResponse;
use WebsiteSQL\Http\Message\RedirectResponse;

class ResponseFactory
{
    /**
     * Create a new response.
     *
     * @param int $code HTTP status code
     * @param string $reasonPhrase Reason phrase to use with the status code
     * @return Response
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): Response
    {
        return new Response($code, [], null, '1.1', $reasonPhrase);
    }

    /**
     * Create a JSON response.
     *
     * @param mixed $data The data to encode as JSON
     * @param int $code HTTP status code
     * @return JsonResponse
     */
    public function createJsonResponse($data, int $code = 200): JsonResponse
    {
        return new JsonResponse($data, $code);
    }

    /**
     * Create an HTML response.
     *
     * @param string $html The HTML content
     * @param int $code HTTP status code
     * @return HtmlResponse
     */
    public function createHtmlResponse(string $html, int $code = 200): HtmlResponse
    {
        return new HtmlResponse($html, $code);
    }

    /**
     * Create a text response.
     *
     * @param string $text The text content
     * @param int $code HTTP status code
     * @return TextResponse
     */
    public function createTextResponse(string $text, int $code = 200): TextResponse
    {
        return new TextResponse($text, $code);
    }

    /**
     * Create a redirect response.
     *
     * @param string $url The URL to redirect to
     * @param int $code HTTP status code
     * @return RedirectResponse
     */
    public function createRedirectResponse(string $url, int $code = 302): RedirectResponse
    {
        return new RedirectResponse($url, $code);
    }

    /**
     * Create an error response.
     *
     * @param string $message Error message
     * @param string $type Error type
     * @param int $code HTTP status code
     * @return JsonResponse
     */
    public function createErrorResponse(string $message, string $type, int $code): JsonResponse
    {
        return $this->createJsonResponse([
            'error' => [
                'message' => $message,
                'type' => $type,
                'code' => $code
            ]
        ], $code);
    }
}
