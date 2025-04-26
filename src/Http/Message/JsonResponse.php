<?php declare(strict_types=1);

namespace WebsiteSQL\Http\Message;

class JsonResponse extends Response
{
    /**
     * Create a new JSON response.
     *
     * @param mixed $data Data to encode as JSON
     * @param int $status HTTP status code
     * @param array $headers HTTP headers
     */
    public function __construct($data, int $status = 200, array $headers = [])
    {
        $json = json_encode($data);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Unable to encode data to JSON: ' . json_last_error_msg());
        }
        
        $body = new Stream(fopen('php://temp', 'r+'));
        $body->write($json);
        $body->rewind();
        
        $headers = array_merge(['Content-Type' => ['application/json; charset=utf-8']], $headers);
        
        parent::__construct($status, $headers, $body);
    }
}
