<?php declare(strict_types=1);

namespace AlanTiller\Framework\Core;

use Dotenv\Dotenv;
use AlanTiller\Framework\Exceptions\ConfigurationException;

class Environment
{
    private array $env = [];

    public function __construct(string $basePath)
    {
        $this->loadEnv($basePath);
    }

    private function loadEnv(string $basePath): void
    {
        try {
            $dotenv = Dotenv::createArrayBacked($basePath);
            $this->env = $dotenv->load();
        } catch (\Exception $e) {
            throw new ConfigurationException('Error loading environment variables: ' . $e->getMessage());
        }
    }

    public function get(string $key, $default = null): mixed
    {
        return $this->env[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->env;
    }
}