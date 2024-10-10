<?php

namespace Konarsky\http\router;

use Konarsky\contracts\RoutesCollectionInterface;

class RoutesCollection implements RoutesCollectionInterface
{
    private array $routes = [];
    private array $globalMiddleware = [];

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function post(string $route, string|callable $controllerAction, array $middleware = []): void
    {
        $this->addRoute('POST', $route, $controllerAction, $middleware);
    }

    public function get(string $route, string|callable $controllerAction, array $middleware = []): void
    {
        $this->addRoute('GET', $route, $controllerAction, $middleware);
    }

    public function delete(string $route, string|callable $controllerAction, array $middleware = []): void
    {
        $this->addRoute('DELETE', $route, $controllerAction, $middleware);
    }

    public function put(string $route, string|callable $controllerAction, array $middleware = []): void
    {
        $this->addRoute('PUT', $route, $controllerAction, $middleware);
    }

    public function patch(string $route, string|callable $controllerAction, array $middleware = []): void
    {
        $this->addRoute('PATCH', $route, $controllerAction, $middleware);
    }

    public function addGlobalMiddleware(array|string $middleware): void
    {
        if (is_array($middleware)) {
            $this->globalMiddleware = array_merge($this->globalMiddleware, $middleware);
        } else {
            $this->globalMiddleware[] = $middleware;
        }
    }

    private function addRoute(string $method, string $route, string|callable $controllerAction, array $middleware): void
    {
        $this->routes[] = [
            'method' => $method,
            'route' => $route,
            'action' => $controllerAction,
            'middleware' => array_merge($this->globalMiddleware, $middleware),
        ];
    }
}