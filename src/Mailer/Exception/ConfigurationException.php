<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Mailer\Exception;

use WebsiteSQL\Framework\Exception\GeneralException;

/**
 * Exception thrown when there's a configuration issue
 */
class ConfigurationException extends GeneralException
{
    /**
     * Error codes
     */
    public const ERROR_MISSING_CONFIG = 10100;
    public const ERROR_INVALID_DRIVER = 10101;
    public const ERROR_MISSING_TEMPLATE_PATH = 10102;
    public const ERROR_MISSING_FROM = 10103;
    public const ERROR_MISSING_FROM_NAME = 10104;
    public const ERROR_MISSING_SMTP_CONFIG = 10105;
    
    /**
     * Create exception for missing configuration
     * 
     * @param string $configKey The configuration key that's missing
     * @return self
     */
    public static function missingConfig(string $configKey): self
    {
        return new self(
            sprintf("Required configuration missing: '%s'", $configKey),
            self::ERROR_MISSING_CONFIG
        );
    }
    
    /**
     * Create exception for invalid mail driver
     * 
     * @param string $driver The invalid driver name
     * @return self
     */
    public static function invalidDriver(string $driver): self
    {
        return new self(
            sprintf("Invalid mail driver: '%s'", $driver),
            self::ERROR_INVALID_DRIVER
        );
    }
}
