<?php declare(strict_types=1);

namespace WebsiteSQL\Config;

class Config {
    private $config = [];
    
    public function add(array $config) {
        $this->config = array_merge($this->config, $config);
        return $this;
    }
    
    public function get($key, $default = null) {
        $keys = explode('.', $key);
        $config = $this->config;
        
        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                return $default;
            }
            $config = $config[$segment];
        }
        
        return $config;
    }
    
    public function set($key, $value) {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                $config[$segment] = $value;
            } else {
                if (!isset($config[$segment]) || !is_array($config[$segment])) {
                    $config[$segment] = [];
                }
                $config = &$config[$segment];
            }
        }
        
        return $this;
    }
}