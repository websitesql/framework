<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Mailer\Exception;

use WebsiteSQL\Framework\Exception\GeneralException;

/**
 * Exception thrown when there's an issue with SMTP
 */
class SmtpException extends GeneralException
{
    /**
     * Error codes
     */
    public const ERROR_CONNECTION_FAILED = 10500;
    public const ERROR_AUTHENTICATION_FAILED = 10501;
    public const ERROR_GENERAL_SMTP = 10502;
    
    /**
     * Create exception for SMTP connection failure
     * 
     * @param string $host The SMTP host
     * @return self
     */
    public static function connectionFailed(string $host): self
    {
        return new self(
            sprintf("Failed to connect to SMTP server: %s", $host),
            self::ERROR_CONNECTION_FAILED
        );
    }
    
    /**
     * Create exception for SMTP authentication failure
     * 
     * @return self
     */
    public static function authenticationFailed(): self
    {
        return new self(
            "SMTP authentication failed",
            self::ERROR_AUTHENTICATION_FAILED
        );
    }
    
    /**
     * Create exception for general SMTP error
     * 
     * @param string $error The error message
     * @return self
     */
    public static function generalError(string $error): self
    {
        return new self(
            sprintf("SMTP error: %s", $error),
            self::ERROR_GENERAL_SMTP
        );
    }
}
