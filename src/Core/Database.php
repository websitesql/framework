<?php declare(strict_types=1);

namespace AlanTiller\Framework\Core;

use Medoo\Medoo;
use AlanTiller\Framework\Exceptions\DatabaseException;

class Database
{
    private ?Medoo $database = null;

    public function __construct(array $config)
    {
        $this->connect($config);
    }

    private function connect(array $config): void
    {
        try {
            $this->database = new Medoo($config);
        } catch (\Exception $e) {
            throw new DatabaseException('Database connection error: ' . $e->getMessage());
        }
    }

    public function getDatabase(): ?Medoo
    {
        return $this->database;
    }
}