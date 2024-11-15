<?php

namespace Konarsky\http\router;

use Psr\Container\ContainerInterface;

class Route
{
    public array $middlewares = [];
    public array $errorMiddleware = [];

    /**
     * @param string|null $method
     * @param string|null $path
     * @param mixed $handler
     * @param mixed $action
     * @param array|null $params
     */
    public function __construct(
        public ?string $method = null,
        public ?string $path = null,
        public mixed $handler = null,
        public mixed $action = null,
        public ?array $params = null,
    ) { }

    /**
     * @param callable|string $middleware
     * @return Route
     */
    public function addMiddleware(callable|string $middleware): Route
    {
        // зарегистрировать мидлвеер как глобальный, участвующий при каждом вызове каждого маршрута
        $this->middlewares[] = [
            'method' => $this->method ?? null,
            'path' => $this->path ?? null,
            'middleware' => $middleware
        ];

        return $this;
    }

    /**
     * @param callable|string $middleware
     * @return Route
     */
    public function addErrorMiddleware(callable|string $middleware): Route
    {
        $this->errorMiddleware = [
            'method' => $this->method ?? null,
            'path' => $this->path ?? null,
            'middleware' => $middleware
        ];

        return $this;
    }
}