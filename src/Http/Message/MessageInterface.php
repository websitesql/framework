<?php declare(strict_types=1);

namespace WebsiteSQL\Http\Message;

/**
 * HTTP messages consist of requests from a client to a server and responses
 * from a server to a client. This interface defines the methods common to
 * each.
 */
interface MessageInterface
{
    /**
     * Gets the HTTP protocol version as a string.
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion(): string;

    /**
     * Returns an instance with the specified HTTP protocol version.
     *
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version);

    /**
     * Gets all message header values.
     *
     * @return string[][] Returns an associative array of the message's headers.
     */
    public function getHeaders(): array;

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given name using a case-insensitive string comparison.
     */
    public function hasHeader($name): bool;

    /**
     * Gets a message header value by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given header.
     */
    public function getHeader($name): array;

    /**
     * Gets a comma-separated string of the values for a single header.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header concatenated with a comma.
     */
    public function getHeaderLine($name): string;

    /**
     * Returns an instance with the provided value replacing the specified header.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     */
    public function withHeader($name, $value);

    /**
     * Returns an instance with the specified header appended with the given value.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     */
    public function withAddedHeader($name, $value);

    /**
     * Returns an instance without the specified header.
     *
     * @param string $name Case-insensitive header field name.
     * @return static
     */
    public function withoutHeader($name);

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody();

    /**
     * Returns an instance with the specified message body.
     *
     * @param StreamInterface $body Body.
     * @return static
     */
    public function withBody($body);
}
