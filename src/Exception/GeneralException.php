<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Exception;

use Exception;

class GeneralException extends Exception
{
	/**
     * Error codes
     */
    public const ERROR_UNKNOWN = 500;

    /**
     * GeneralException constructor.
     *
     * @param string         $message  Optional custom message, defaulting to a standard message.
     * @param int            $code     Optional HTTP status code, defaulting to 500.
     * @param Throwable|null $previous Previous throwable, if any.
     */
    public function __construct(
        string $message = 'An error occurred while processing your request.',
        int $code = self::ERROR_UNKNOWN,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Get a human-readable representation of the error
     * 
     * @return string
     */
    public function getErrorDescription(): string
    {
        return sprintf('[%d] %s', $this->code, $this->message);
    }
}