<?php declare(strict_types=1);

namespace WebsiteSQL\Http\Message;

/**
 * Representation of an incoming, server-side HTTP request.
 */
interface ServerRequestInterface extends RequestInterface
{
    /**
     * Retrieve server parameters.
     *
     * @return array
     */
    public function getServerParams(): array;

    /**
     * Retrieve cookies.
     *
     * @return array
     */
    public function getCookieParams(): array;

    /**
     * Return an instance with the specified cookies.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     * @return static
     */
    public function withCookieParams(array $cookies);

    /**
     * Retrieve query string arguments.
     *
     * @return array
     */
    public function getQueryParams(): array;

    /**
     * Return an instance with the specified query string arguments.
     *
     * @param array $query Array of query string arguments.
     * @return static
     */
    public function withQueryParams(array $query);

    /**
     * Retrieve normalized file upload data.
     *
     * @return array An array tree of UploadedFileInterface instances.
     */
    public function getUploadedFiles(): array;

    /**
     * Create a new instance with the specified uploaded files.
     *
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles);

    /**
     * Retrieve any parameters provided in the request body.
     *
     * @return null|array|object The deserialized body parameters, if any.
     */
    public function getParsedBody();

    /**
     * Return an instance with the specified body parameters.
     *
     * @param null|array|object $data The deserialized body data.
     * @return static
     */
    public function withParsedBody($data);

    /**
     * Retrieve attributes derived from the request.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes(): array;

    /**
     * Retrieve a single derived request attribute.
     *
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     */
    public function getAttribute($name, $default = null);

    /**
     * Return an instance with the specified derived request attribute.
     *
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     * @return static
     */
    public function withAttribute($name, $value);

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * @param string $name The attribute name.
     * @return static
     */
    public function withoutAttribute($name);
}
