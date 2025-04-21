<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Mailer\Exception;

use WebsiteSQL\Framework\Exception\GeneralException;

/**
 * Exception thrown when there's an issue with templates
 */
class TemplateException extends GeneralException
{
    /**
     * Error codes
     */
    public const ERROR_TEMPLATE_NOT_FOUND = 10200;
    public const ERROR_TEMPLATE_RENDERING = 10201;
    
    /**
     * Create exception for template not found
     * 
     * @param string $template The template name
     * @return self
     */
    public static function templateNotFound(string $template): self
    {
        return new self(
            sprintf("Template not found: '%s'", $template),
            self::ERROR_TEMPLATE_NOT_FOUND
        );
    }
    
    /**
     * Create exception for template rendering error
     * 
     * @param string $error The error message
     * @return self
     */
    public static function renderingError(string $error): self
    {
        return new self(
            sprintf("Failed to render template: %s", $error),
            self::ERROR_TEMPLATE_RENDERING
        );
    }
}
