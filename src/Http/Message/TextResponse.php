<?php declare(strict_types=1);

namespace WebsiteSQL\Http\Message;

class TextResponse extends Response
{
    /**
     * Create a new plain text response.
     *
     * @param string $text Text content
     * @param int $status HTTP status code
     * @param array $headers HTTP headers
     */
    public function __construct(string $text, int $status = 200, array $headers = [])
    {
        $body = new Stream(fopen('php://temp', 'r+'));
        $body->write($text);
        $body->rewind();
        
        $headers = array_merge(['Content-Type' => ['text/plain; charset=utf-8']], $headers);
        
        parent::__construct($status, $headers, $body);
    }
}
