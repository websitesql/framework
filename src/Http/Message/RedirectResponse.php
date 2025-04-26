<?php declare(strict_types=1);

namespace WebsiteSQL\Http\Message;

class RedirectResponse extends Response
{
    /**
     * Create a new redirect response.
     *
     * @param string $url URL to redirect to
     * @param int $status HTTP status code (usually 301, 302, 303, 307, 308)
     * @param array $headers HTTP headers
     */
    public function __construct(string $url, int $status = 302, array $headers = [])
    {
        $headers = array_merge(['Location' => [$url]], $headers);
        
        parent::__construct($status, $headers);
    }
}
