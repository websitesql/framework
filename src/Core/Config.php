<?php declare(strict_types=1);

namespace AlanTiller\Framework\Core;

use AlanTiller\Framework\Exceptions\ConfigurationException;
use Symfony\Component\Yaml\Yaml;

class Config
{
    private array $config = [];
    private string $configPath;

    public function __construct(string $configPath, array $initialConfig = [])
    {
        $this->configPath = $configPath;
        $this->loadConfigFiles();
        $this->config = array_merge_recursive($this->config, $initialConfig);
    }

    private function loadConfigFiles(): void
    {
        $files = glob($this->configPath . '/*.php');

        if ($files === false) {
            throw new ConfigurationException("Could not find config files in {$this->configPath}");
        }

        foreach ($files as $file) {
            $filename = basename($file, '.php');
            $this->config[$filename] = require $file;
        }
    }

    public function get(string $key, $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (!isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $segment) {
            if (!isset($config[$segment]) || !is_array($config[$segment])) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }

        $config = $value;
    }

    public function all(): array
    {
        return $this->config;
    }
}