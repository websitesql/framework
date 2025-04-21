<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Mailer\Exception;

use WebsiteSQL\Framework\Exception\GeneralException;

/**
 * Exception thrown when there's an issue sending the email
 */
class SendException extends GeneralException
{
    /**
     * Error codes
     */
    public const ERROR_GENERAL_SEND_FAILURE = 10300;
    public const ERROR_MAIL_FUNCTION_FAILED = 10301;
    public const ERROR_LOG_FAILED = 10302;
    
    /**
     * Create exception for general send failure
     * 
     * @param string $error The error message
     * @return self
     */
    public static function generalFailure(string $error): self
    {
        return new self(
            sprintf("Failed to send email: %s", $error),
            self::ERROR_GENERAL_SEND_FAILURE
        );
    }
    
    /**
     * Create exception for mail() function failure
     * 
     * @return self
     */
    public static function mailFunctionFailed(): self
    {
        return new self(
            "Failed to send email via mail() function",
            self::ERROR_MAIL_FUNCTION_FAILED
        );
    }
    
    /**
     * Create exception for log failure
     * 
     * @return self
     */
    public static function logFailed(): self
    {
        return new self(
            "Failed to log email",
            self::ERROR_LOG_FAILED
        );
    }
}
