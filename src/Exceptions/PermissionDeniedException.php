<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Exceptions;

use Exception;
use Throwable;

class PermissionDeniedException extends GeneralException
{
    /**
     * PermissionDeniedException constructor.
     *
     * @param string         $message  Optional custom message, defaulting to a standard message.
     * @param int            $code     Optional HTTP status code, defaulting to 404.
     * @param Throwable|null $previous Previous throwable, if any.
     */
    public function __construct(
        string $message = 'You do not have permission to access this resource.',
        int $code = 403,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}