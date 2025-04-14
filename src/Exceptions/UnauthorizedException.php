<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Exceptions;

use Throwable;

class UnauthorizedException extends GeneralException
{
    /**
     * UnauthorizedException constructor.
     *
     * @param string         $message  Optional custom message, defaulting to a standard message.
     * @param int            $code     Optional HTTP status code, defaulting to 401.
     * @param Throwable|null $previous Previous throwable, if any.
     */
    public function __construct(
        string $message = 'The request requires user authentication, please authenticate and try again.',
        int $code = 401,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}