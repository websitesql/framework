<?php declare(strict_types=1);

namespace WebsiteSQL\Http\Message;

/**
 * Representation of an outgoing, client-side request.
 */
interface RequestInterface extends MessageInterface
{
    /**
     * Retrieves the message's request target.
     *
     * @return string
     */
    public function getRequestTarget(): string;

    /**
     * Returns an instance with the specific request-target.
     *
     * @param mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget);

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod(): string;

    /**
     * Returns an instance with the provided HTTP method.
     *
     * @param string $method Case-sensitive method.
     * @return static
     */
    public function withMethod($method);

    /**
     * Retrieves the URI instance.
     *
     * @return UriInterface Returns a UriInterface instance representing the URI of the request.
     */
    public function getUri();

    /**
     * Returns an instance with the provided URI.
     *
     * @param UriInterface $uri New request URI.
     * @param bool $preserveHost Preserve the original state of the Host header.
     * @return static
     */
    public function withUri($uri, $preserveHost = false);
}
