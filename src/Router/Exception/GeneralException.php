<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Router\Exception;

use Exception;

class GeneralException extends Exception
{
    /**
     * GeneralException constructor.
     *
     * @param string         $message  Optional custom message, defaulting to a standard message.
     * @param int            $code     Optional HTTP status code, defaulting to 500.
     * @param Throwable|null $previous Previous throwable, if any.
     */
    public function __construct(
        string $message = 'An error occurred while processing your request.',
        int $code = 500,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}