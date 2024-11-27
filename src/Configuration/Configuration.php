<?php

namespace Konarsky\Configuration;

readonly class Configuration implements ConfigurationInterface
{
   public function __construct(private array $config = []) { }

    public function get($key, $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function has($key): mixed
    {
        return isset($this->config[$key]);
    }
}