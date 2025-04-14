<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Exceptions;

use Throwable;

class MissingRequiredFieldsException extends GeneralException
{
    /**
     * MissingRequiredFieldsException constructor.
     *
     * @param string         $message  Optional custom message, defaulting to a standard message.
     * @param int            $code     Optional HTTP status code, defaulting to 400.
     * @param Throwable|null $previous Previous throwable, if any.
     */
    public function __construct(
        string $message = 'Missing required fields, please check your request and try again.',
        int $code = 400,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}