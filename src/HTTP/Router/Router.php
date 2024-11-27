<?php

namespace Konarsky\HTTP\Router;

use InvalidArgumentException;
use Konarsky\Contracts\ErrorMiddlewareInterface;
use Konarsky\Contracts\HTTPRouterInterface;
use Konarsky\Contracts\MiddlewareInterface;
use Konarsky\HTTP\Exceptions\NotFoundHttpException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

class Router implements HTTPRouterInterface
{
    protected array $routes = [];
    protected array $globalMiddlewares = [];
    protected array $errorMiddlewares = [];
    protected array $prefix = [];
    protected array $targetForMiddleware;
    protected string $methodPath;

    public function __construct(private ContainerInterface $container) { }

    /**
     * Регистрация глобального мидлвеера
     *
     * @param string|callable $middleware коллбек функция или неймспейс класса мидлвеера
     *
     * @return HTTPRouterInterface
     */
    public function addGlobalMiddleware(callable|string $middleware): HTTPRouterInterface
    {
        $this->globalMiddlewares[] = [
            'middleware' => $middleware,
        ];

        return $this;
    }

    /**
     * Добавление маршрута для метода GET
     *
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function get(string $route, string|callable $handler): Route
    {
        return $this->add('GET', $route, $handler);
    }

    /**
     * Добавление маршрута для метода POST
     *
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function post(string $route, string|callable $handler): Route
    {
        return $this->add('POST', $route, $handler);
    }

    /**
     * Добавление маршрута для метода PUT
     *
     * @param string $route путь
     * @param string|callable $handler обработчик, коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function put(string $route, string|callable $handler): Route
    {
        return $this->add('PUT', $route, $handler);
    }

    /**
     * Добавление маршрута для метода PATCH
     *
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function patch(string $route, string|callable $handler): Route
    {
        return $this->add('PATCH', $route, $handler);
    }


    /**
     * Добавление маршрута для метода DELETE
     *
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function delete(string $route, string|callable $handler): Route
    {
        return $this->add('DELETE', $route, $handler);
    }

    /**
     * Добавление группы машрутов
     *
     * Пример:
     * /api/v1/path
     * $router->group('api', function (HTTPRouterInterface $router) {
     *     $router->group('v1', function (HTTPRouterInterface $router) {
     *         $router->get('/path', SomeHandler::class . '::action');
     *     });
     * });
     *
     */
    public function group(string $name, callable $handler): Route
    {
        $path = null;

        $previousPrefix = $this->prefix;
        $prefixNow = trim($name, '/');
        $this->prefix[] = $prefixNow;

        $handler($this);

        foreach ($this->prefix as $prefix) {
            $path .= $prefix . '/';
        }

        $this->prefix = $previousPrefix;
        $this->methodPath = $name;

        return $this->routes['groups'][$path] = new Route(path: $path);
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
     * @param string $method метод запроса
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function add(string $method, string $route, string|callable $handler): Route
    {
        $path = '/';
        foreach ($this->prefix as $prefix) {
            $path .= $prefix . '/';
        }

        $this->methodPath = trim(trim(strtok($route, '{')), '/');
        $path .= $this->methodPath;

        [$handler, $action] = $this->resolveHandler($handler);

        return $this->routes[$method][$path] = new Route(
            $method,
            $path,
            $handler,
            $action,
            $this->prepareParams($route),
        );
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
     * Диспетчеризация входящего запроса 🤢
     *
     * @param RequestInterface $request объект запроса
     *
     * @return mixed
     * @throws NotFoundHttpException если маршрут не зарегистрирован в конфигурации машрутов
     * @throws Throwable
     */
    public function dispatch(RequestInterface $request): mixed
    {
        $httpMethod = $request->getMethod();
        $requestPath = $request->getUri()->getPath();
        $route = null;

        try {
            if (isset($this->routes[$httpMethod][$requestPath]) === false) {
                throw new NotFoundHttpException('Страница не найдена', 404);
            }

            $route = $this->routes[$httpMethod][$requestPath];

            $this->applyMiddlewares($route);

            $params = $this->mapParams($request->getQueryParams(), $route->params);

            // вызов обработчика с передачей параметров из запроса
            $controller = $this->container->get($route->handler);
            $action = $route->action;

            return $controller->$action(...$params);
        } catch (Throwable $error) {
            $this->applyErrorMiddleware($error, $requestPath, $route);

            throw $error;
        }
    }

    /**
     * @param mixed $middleware
     * @return MiddlewareInterface|ErrorMiddlewareInterface
     */
    private function buildMiddlewareInstance(mixed $middleware): MiddlewareInterface|ErrorMiddlewareInterface
    {
        if (is_string($middleware)) {
            $middleware = $this->container->build($middleware);
        }

        if (
            $middleware instanceof MiddlewareInterface === false
            && $middleware instanceof ErrorMiddlewareInterface === false
        ) {
            throw new InvalidArgumentException('Middleware должен реализовывать ' . MiddlewareInterface::class);
        }

        return $middleware;
    }

    /**
     * @param array $middlewares
     * @return void
     */
    private function executeMiddlewares(array $middlewares): void
    {
        foreach ($middlewares as $middlewareConfig) {
            $middlewareInstance = $this->buildMiddlewareInstance($middlewareConfig['middleware']);
            $middlewareInstance($this->container->get(RequestInterface::class));
        }
    }

    /**
     * @param Route $route
     * @return void
     */
    private function applyMiddlewares(Route $route): void
    {
        $groupRoutes = $this->routes['groups'];

        $this->executeMiddlewares($route->middlewares);

        foreach ($groupRoutes as $groupPath => $groupRoute) {
            if (str_contains($route->path, $groupPath) === false) {
                continue;
            }

            $this->executeMiddlewares($groupRoute->middlewares);
        }

        $this->executeMiddlewares($this->globalMiddlewares);
    }

    /**
     * @param array|null $middlewareConfig Конфигурация middleware для ошибок.
     * @param Throwable $error Объект ошибки.
     * @return bool true, если middleware было успешно выполнено, иначе false.
     */
    private function invokeErrorMiddleware(?array $middlewareConfig, Throwable $error): bool
    {
        if (empty($middlewareConfig) === true) {
            return false;
        }

        $errorMiddlewareInstance = $this->buildMiddlewareInstance($middlewareConfig['middleware']);
        $errorMiddlewareInstance($error);

        return true;
    }

    /**
     * @param Throwable $error
     * @param string $path
     * @param Route|null $route
     * @return void
     */
    private function applyErrorMiddleware(Throwable $error, string $path, ?Route $route):void
    {
        $groupRoutes = $this->routes['groups'];

        if (isset($route) === true && $this->invokeErrorMiddleware($route->errorMiddleware, $error)) {
            return;
        }

        foreach ($groupRoutes as $groupPath => $groupRoute) {
            if (str_contains($path, $groupPath) === false) {
                continue;
            }

            if ($this->invokeErrorMiddleware($groupRoute->errorMiddleware, $error)) {
                return;
            }
        }
    }
}
