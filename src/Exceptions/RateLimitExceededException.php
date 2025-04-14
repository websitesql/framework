<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Exceptions;

use Throwable;

class RateLimitExceededException extends GeneralException
{
    /**
     * RateLimitExceededException constructor.
     *
     * @param string         $message  Optional custom message, defaulting to a standard message.
     * @param int            $code     Optional HTTP status code, defaulting to 400.
     * @param Throwable|null $previous Previous throwable, if any.
     */
    public function __construct(
        string $message = 'Too many requests. Please try again later.',
        int $code = 429,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}