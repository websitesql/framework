<?php declare(strict_types=1);

namespace WebsiteSQL\Http\Message;

class Response implements ResponseInterface
{
    private $statusCode = 200;
    private $reasonPhrase = '';
    private $headers = [];
    private $body;
    private $protocolVersion = '1.1';

    public function __construct(
        int $status = 200,
        array $headers = [],
        StreamInterface $body = null,
        string $version = '1.1',
        string $reason = ''
    ) {
        $this->statusCode = $status;
        $this->headers = $headers;
        $this->body = $body ?: new Stream(fopen('php://temp', 'r+'));
        $this->protocolVersion = $version;
        $this->reasonPhrase = $reason ?: $this->getReasonPhraseByCode($status);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        $clone = clone $this;
        $clone->statusCode = $code;
        $clone->reasonPhrase = $reasonPhrase ?: $this->getReasonPhraseByCode($code);
        return $clone;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version): MessageInterface
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

    public function withHeader($name, $value): MessageInterface
    {
        $clone = clone $this;
        $name = strtolower($name);
        $clone->headers[$name] = is_array($value) ? $value : [$value];
        return $clone;
    }

    public function withAddedHeader($name, $value): MessageInterface
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

    public function withoutHeader($name): MessageInterface
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

    public function withBody($body): MessageInterface
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    /**
     * Write content to the response body.
     *
     * @param string $content
     * @return self
     */
    public function write(string $content): self
    {
        $body = new Stream(fopen('php://temp', 'r+'));
        $body->write($content);
        $body->rewind();
        
        return $this->withBody($body);
    }

    /**
     * Create a JSON response.
     *
     * @param mixed $data Data to encode
     * @param int $status HTTP status code
     * @return JsonResponse
     */
    public function json($data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    /**
     * Create an HTML response.
     *
     * @param string $html HTML content
     * @param int $status HTTP status code
     * @return HtmlResponse
     */
    public function html(string $html, int $status = 200): HtmlResponse
    {
        return new HtmlResponse($html, $status);
    }

    /**
     * Create a plain text response.
     *
     * @param string $text Text content
     * @param int $status HTTP status code
     * @return TextResponse
     */
    public function text(string $text, int $status = 200): TextResponse
    {
        return new TextResponse($text, $status);
    }

    /**
     * Create a redirect response.
     *
     * @param string $url URL to redirect to
     * @param int $status HTTP status code
     * @return RedirectResponse
     */
    public function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Emit the response to the client.
     */
    public function emit(): void
    {
        // Send status code
        http_response_code($this->statusCode);
        
        // Send headers
        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }
        
        // Send body
        echo $this->body;
    }

    /**
     * Get a standard reason phrase for a status code.
     *
     * @param int $code
     * @return string
     */
    private function getReasonPhraseByCode(int $code): string
    {
        $phrases = [
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        ];
        
        return $phrases[$code] ?? '';
    }
}
