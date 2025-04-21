<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Mailer\Exception;

use WebsiteSQL\Framework\Exception\GeneralException;

/**
 * Exception thrown when there's an issue with email content
 */
class ContentException extends GeneralException
{
    /**
     * Error codes
     */
    public const ERROR_MISSING_SUBJECT = 10400;
    public const ERROR_MISSING_CONTENT = 10401;
    public const ERROR_INVALID_ATTACHMENT = 10402;
    
    /**
     * Create exception for missing subject
     * 
     * @return self
     */
    public static function missingSubject(): self
    {
        return new self(
            "Email subject is required",
            self::ERROR_MISSING_SUBJECT
        );
    }
    
    /**
     * Create exception for missing content
     * 
     * @return self
     */
    public static function missingContent(): self
    {
        return new self(
            "Email content is required (HTML or plain text)",
            self::ERROR_MISSING_CONTENT
        );
    }
}
