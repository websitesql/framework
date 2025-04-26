<?php declare(strict_types=1);

namespace WebsiteSQL\Http\Message;

class HtmlResponse extends Response
{
    /**
     * Create a new HTML response.
     *
     * @param string $html HTML content
     * @param int $status HTTP status code
     * @param array $headers HTTP headers
     */
    public function __construct(string $html, int $status = 200, array $headers = [])
    {
        $body = new Stream(fopen('php://temp', 'r+'));
        $body->write($html);
        $body->rewind();
        
        $headers = array_merge(['Content-Type' => ['text/html; charset=utf-8']], $headers);
        
        parent::__construct($status, $headers, $body);
    }
}
