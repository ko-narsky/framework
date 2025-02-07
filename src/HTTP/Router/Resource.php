<?php

declare(strict_types=1);

namespace Konarsky\HTTP\Router;

use Konarsky\Contract\HTTPRouterInterface;

readonly class Resource
{
    /**
     * @param string $name
     * @param string $controller
     * @param array $config
     */
    public function __construct(
        private string $name,
        private string $controller,
        private array  $config = []
    ) { }

    public function build(HTTPRouterInterface $router): Route
    {
        $path = $this->name;
        $config = $this->setConfig($path);

        foreach ($config as $params) {
            $route = $router->add($params['method'], $params['path'], $this->controller . '::' . $params['action']);

            if (empty($params['middleware']) === true) {
                continue;
            }

            foreach ($params['middleware'] as $middleware) {
                $route->addMiddleware($middleware);
            }
        }

        return $router->group($path);
    }

    private function setConfig($path): array
    {
        $config = [
            'index' => [
                'method' => 'GET',
                'path' => $path,
                'action' => 'actionList',
                'middleware' => [],
            ],
            'view' => [
                'method' => 'GET',
                'path' => "{$path}/{:id|integer}",
                'action' => 'actionView',
                'middleware' => [],
            ],
            'create' => [
                'method' => 'POST',
                'path' => $path,
                'action' => 'actionCreate',
                'middleware' => [],
            ],
            'put' => [
                'method' => 'PUT',
                'path' => "{$path}/{:id|integer}",
                'action' => 'actionUpdate',
                'middleware' => [],
            ],
            'patch' => [
                'method' => 'PATCH',
                'path' => "{$path}/{:id|integer}",
                'action' => 'actionPatch',
                'middleware' => [],
            ],
            'delete' => [
                'method' => 'DELETE',
                'path' => "{$path}/{:id|integer}",
                'action' => 'actionDelete',
                'middleware' => [],
            ],
        ];

        foreach ($this->config as $method => $newElement) {
            if (isset($config[$method]) === false) {
                $config[$method] = $newElement;

                continue;
            }

            $config[$method] = array_merge($config[$method], $newElement);
        }

        return $config;
    }
}