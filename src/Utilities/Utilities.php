<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Utilities;

use WebsiteSQL\Framework\Utilities\Factories\UuidFactory;
use WebsiteSQL\Framework\Utilities\Factories\StringFactory;
use WebsiteSQL\Framework\Utilities\Factories\DateTimeFactory;
use WebsiteSQL\Framework\Utilities\Factories\PaginationFactory;
use WebsiteSQL\Framework\Utilities\Factories\SecurityFactory;
use WebsiteSQL\Framework\Utilities\Factories\FileFactory;
use WebsiteSQL\Framework\Utilities\Factories\ValidationFactory;

class Utilities
{
    // Factory methods to create instances
    public static function uuid(): UuidFactory
    {
        return new UuidFactory();
    }
    
    public static function string(string $initialValue = ''): StringFactory
    {
        return new StringFactory($initialValue);
    }
    
    public static function datetime(string $datetime = null): DateTimeFactory
    {
        return new DateTimeFactory($datetime);
    }
    
    public static function pagination(array $data = []): PaginationFactory
    {
        return new PaginationFactory($data);
    }
    
    public static function security(): SecurityFactory
    {
        return new SecurityFactory();
    }
    
    public static function file(string $filePath = null): FileFactory
    {
        return new FileFactory($filePath);
    }
    
    public static function validation(): ValidationFactory
    {
        return new ValidationFactory();
    }
}