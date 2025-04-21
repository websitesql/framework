<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Router\Exception;

use Exception;
use Throwable;

class NotFoundException extends GeneralException
{
    /**
     * NotFoundException constructor.
     *
     * @param string         $message  Optional custom message, defaulting to a standard message.
     * @param int            $code     Optional HTTP status code, defaulting to 404.
     * @param Throwable|null $previous Previous throwable, if any.
     */
    public function __construct(
        string $message = 'The requested resource could not be found.',
        int $code = 404,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}