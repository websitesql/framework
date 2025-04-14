<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Exceptions;

use Exception;
use Throwable;

class MethodNotAllowedException extends GeneralException
{
    /**
     * MethodNotAllowedException constructor.
     *
     * @param string         $message  Optional custom message, defaulting to a standard message.
     * @param int            $code     Optional HTTP status code, defaulting to 404.
     * @param Throwable|null $previous Previous throwable, if any.
     */
    public function __construct(
        string $message = 'The requested resource does not support the HTTP method used.',
        int $code = 405,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}