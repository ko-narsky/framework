<?php

namespace Konarsky\http\router;

class Route
{
    /**
     * @param string $method
     * @param string $path
     * @param mixed $handler
     * @param mixed $action
     * @param array $params
     */
    public function __construct(
        public string $method,
        public string $path,
        public mixed $handler,
        public mixed $action,
        public array $params
    )
    { }
}