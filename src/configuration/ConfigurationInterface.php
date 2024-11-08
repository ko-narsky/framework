<?php

declare(strict_types=1);

namespace Konarsky\configuration;

interface ConfigurationInterface
{
    function get($key, $default): mixed;
    function has($key): mixed;
}
