<?php

namespace Konarsky\http\router;

use Konarsky\contracts\HTTPRouterInterface;
use Konarsky\http\exception\NotFoundHttpException;
use Konarsky\middleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

class Router implements HTTPRouterInterface
{
    protected array $routes = [];
    protected array $middlewares = [];
    protected array $errorMiddlewares = [];
    protected array $prefix = [];
    protected array $targetForMiddleware;
    protected string $methodPath;

    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * Регистрация глобального мидлвеера
     *
     * @param string|callable $middleware коллбек функция или неймспейс класса мидлвеера
     *
     * @return HTTPRouterInterface
     * @throws \ReflectionException
     */
    public function addMiddleware(callable|string $middleware): HTTPRouterInterface
    {
        // Проверять неймспейс класса на соответствие MiddlewareInterface, при несоответствии выбрасывать ошибку
        if (is_string($middleware)) {
            $middleware = $this->container->build($middleware);
        }

        if ($middleware instanceof MiddlewareInterface === false) {
            throw new \InvalidArgumentException('Middleware должен реализовывать ' . MiddlewareInterface::class);
        }

        // зарегистрировать мидлвеер как глобальный, участвующий при каждом вызове каждого маршрута
        $this->middlewares[] = [
            'method' => $this->targetForMiddleware['method'] ?? null,
            'path' => $this->targetForMiddleware['path'] ?? null,
            'middleware' => $middleware
        ];

        return $this;
    }

    /**
     * Регистрация мидлвеера для обработки ошибок.
     *
     * @param callable|string $middleware
     * @return void
     */
    public function addErrorMiddleware(callable|string $middleware): HTTPRouterInterface
    {
        if (is_string($middleware)) {
            $middleware = $this->container->build($middleware);
        }
//
//        if ($middleware instanceof MiddlewareInterface === false) {
//            throw new \InvalidArgumentException('Middleware должен реализовывать ' . MiddlewareInterface::class);
//        }

        $this->errorMiddlewares[] = $middleware;

        return $this;
    }

    /**
     * Добавление маршрута для метода GET
     *
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Router
     */
    public function get(string $route, string|callable $handler): self
    {
        $this->add('GET', $route, $handler);

        return $this;
    }

    /**
     * Добавление маршрута для метода POST
     *
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Router
     */
    public function post(string $route, string|callable $handler): self
    {
        $this->add('POST', $route, $handler);

        return $this;
    }

    /**
     * Добавление маршрута для метода PUT
     *
     * @param  string $route путь
     * @param  string|callable $handler обработчик, коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Router
     */
    public function put(string $route, string|callable $handler): self
    {
        $this->add('PUT', $route, $handler);

        return $this;
    }

    /**
     * Добавление маршрута для метода PATCH
     *
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Router
     */
    public function patch(string $route, string|callable $handler): self
    {
        $this->add('PATCH', $route, $handler);

        return $this;
    }

    /**
     * Добавление маршрута для метода DELETE
     *
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Router
     */
    public function delete(string $route, string|callable $handler): self
    {
        $this->add('DELETE', $route, $handler);

        return $this;
    }

    /**
     * Добавление группы машрутов
     *
     * Пример:
     * /api/v1/path
     * $router->group('api', function (HTTPRouterInterface $router) {
     *
     *     $router->group('v1', function (HTTPRouterInterface $router) {
     *
     *         $router->get('/path', SomeHandler::class . '::action');
     *
     *     });
     *
     * });
     *
     */
    public function group(string $name, callable $handler): self
    {
        $previousPrefix = $this->prefix;

        $prefixNow = trim($name, '/');

        $this->prefix[] = $prefixNow;

        $handler($this);

        $this->prefix = $previousPrefix;

        $this->targetForMiddleware['path'] = str_replace(
            '/' . trim($this->methodPath, '/'),
            '',
            $this->targetForMiddleware['path']
        );
        $this->targetForMiddleware['method'] = null;

        $this->methodPath = $name;

        return $this;
    }

    /**
     * Получение параметров запроса из маршрута
     *
     * @param  string $route маршрут
     * Пример:
     * "/path {firstNumber} {?secondNumber=900}"
     * @return array
     * Пример:
     * [
     *     [
     *         'name' => 'firstNumber',
     *         'required' => true,
     *         'default' => null,
     *     ],
     *     [
     *         'name' => 'secondNumber',
     *         'required' => false,
     *         'default' => 900,
     *     ],
     * ]
     */
    private function prepareParams(string $route): array
    {
        // Пример простой логики для извлечения параметров
        preg_match_all('/{(\??\w+)(?:=(\w+))?}/', $route, $matches, PREG_SET_ORDER);
        $params = [];

        foreach ($matches as $match) {
            $params[] = [
                'name' => $match[0][1] !== '?' ? $match[1] : substr($match[1], 1),
                'required' => $match[0][1] !== '?',
                'default' => $match[2] ?? null,
            ];
        }

        return $params;
    }

    /**
     * Формирование массива параметров вызовов обработчика маршрута
     *
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return array
     * Пример для callable:
     * [$handler, null]
     * Пример для string:
     * ['Неймспейс', 'метод'];
     */
    private function resolveHandler(callable|string $handler): array
    {
        if (is_callable($handler)) {
            return [$handler, null];
        }

        if (is_string($handler)) {
            if (str_contains($handler, '::')) {
                return explode('::', $handler);
            }
        }

        throw new \InvalidArgumentException('Неверный формат обработчика');
    }

    /**
     * Добавление маршрута для метода запроса
     *
     * @param  string $method метод запроса
     * @param  string $route путь
     * @param  string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return void
     */
    public function add(string $method, string $route, string|callable $handler): void
    {
        $path = '/';
        foreach ($this->prefix as $prefix) {
            $path .= $prefix . '/';
        }

        $this->methodPath = trim(trim(strtok($route, '{')), '/');
        $path .= $this->methodPath;

        [$handler, $action] = $this->resolveHandler($handler);

        $this->routes[$method][$path] = new Route(
            $method,
            $path,
            $handler,
            $action,
            $this->prepareParams($route),
        );

        $this->targetForMiddleware = [
            'method' => $method,
            'path' => $path
        ];
    }

    /**
     * Получение значений параметров запроса определенных для маршрута
     *
     * Пример:
     * "/path {firstNumber} {?secondNumber=900}"
     * "/path?firstNumber=700"
     *
     * @param  array $queryParams параметры из запроса
     * @param  array $params подготовленные параметры определенных для запроса
     * @return array
     * Пример:
     * [700, 900]
     * @throws \InvalidArgumentException если в строке запроса не передан параметр объявленный как обязательный
     */
    private function mapParams(array $queryParams, array $params): array
    {
        $result = [];

        foreach ($params as $param) {
            if (isset($queryParams[$param['name']])) {
                $result[] = $queryParams[$param['name']];

                continue;
            }
            if (isset($param['default']) === true) {
                $result[] = $param['default'];

                continue;
            }
            if ($param['required'] === false) {
                $result[] = null;

                continue;
            }

            throw new \InvalidArgumentException("Обязательный параметр {$param['name']} не найден в запросе");
        }

        return $result;
    }

    /**
     * Диспетчеризация входящего запроса
     *
     * @param  RequestInterface $request объект запроса
     *
     * @return mixed
     * @throws NotFoundHttpException если маршрут не зарегистрирован в конфигурации машрутов
     */
    public function dispatch(RequestInterface $request): mixed
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        try {
            // поиск конфигурации маршрута для пути входящего запроса
            if (isset($this->routes[$method][$path]) === false) {
                throw new NotFoundHttpException('Страница не найдена', 404);
            }

            $route = $this->routes[$method][$path];

            // применение мидлвееров назначенных как глобальных или для группы, так и для конкретного эндпоинта
            foreach ($this->middlewares as $middleware) {
                if (
                    ($middleware['method'] === null || $middleware['method'] === $method)
                    && ($middleware['path'] === null || str_contains($path, $middleware['path']) === true)
                ) {
                    $middleware['middleware']($this->container->get(RequestInterface::class));
                }
            }

            $params = $this->mapParams($request->getQueryParams(), $route->params);

            // вызов обработчика с передачей параметров из запроса
            $controller = $this->container->get($route->handler);
            $action = $route->action;

            return $controller->$action(...$params);
        } catch (Throwable $e) {
            foreach ($this->errorMiddlewares as $errorMiddleware) {
                $errorMiddleware($e, $this->container->get(RequestInterface::class));
            }

            throw $e;
        }
    }
}
