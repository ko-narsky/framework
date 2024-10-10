<?php

namespace Konarsky\contracts;
interface RoutesCollectionInterface
{
    public function getRoutes(): array;

    public function post(string $route, string|callable $controllerAction, array $middleware = []): void;

    public function get(string $route, string|callable $controllerAction, array $middleware = []): void;

    public function delete(string $route, string|callable $controllerAction, array $middleware = []): void;

    public function put(string $route, string|callable $controllerAction, array $middleware = []): void;

    public function patch(string $route, string|callable $controllerAction, array $middleware = []): void;

    public function addGlobalMiddleware(array|string $middleware): void;

}