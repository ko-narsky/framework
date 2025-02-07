<?php

namespace Konarsky\Configuration;

class Configuration implements ConfigurationInterface
{
    private array $configs = [];

    public function __construct(...$configs)
    {
        foreach ($configs as $config) {
            $this->configs = array_merge($this->configs, $config);
        }
    }

    public function get($key, $default = null): mixed
    {
        return $this->resolveKey($key) ?? $default;
    }

    public function has($key): mixed
    {
        return $this->resolveKey($key) !== null;
    }

    private function resolveKey(string $keyPath): ?string
    {
        $keys = explode('.', $keyPath);
        $result = $this->configs;

        foreach ($keys as $key) {
            if (is_array($result) && isset($result[$key]) === true) {
                $result = $result[$key];

                continue;
            }

            return null;
        }

        return $result;
    }
}