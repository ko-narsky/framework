<?php

declare(strict_types=1);

namespace Konarsky\Configuration;

interface ConfigurationInterface
{
    function get($key, $default): mixed;
    function has($key): mixed;
}
