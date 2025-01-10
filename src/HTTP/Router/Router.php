<?php

namespace Konarsky\HTTP\Router;

use InvalidArgumentException;
use Konarsky\Contract\ErrorMiddlewareInterface;
use Konarsky\Contract\HTTPRouterInterface;
use Konarsky\Contract\MiddlewareInterface;
use Konarsky\Exception\HTTP\BadRequestHttpException;
use Konarsky\Exception\HTTP\NotFoundHttpException;
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

    public function addResource(string $name, string $controller, array $config = null): Route
    {
        return (new Resource($name, $controller, $config))->build($this);
    }

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
    public function group(string $name, callable $handler = null): Route
    {
        $path = null;

        $previousPrefix = $this->prefix;
        $prefixNow = trim($name, '/');
        $this->prefix[] = $prefixNow;

        if ($handler !== null) {
            $handler($this);
        }

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
        preg_match_all('/{(\??\w+)(?:\|(\w+))?(?:=(\w+))?}/', $route, $matches, PREG_SET_ORDER);
        $params = [];

        foreach ($matches as $match) {
            $params[] = [
                'name' => $match[1] !== '?' ? $match[1] : substr($match[1], 1),
                'required' => $match[1][0] !== '?',
                'type' => $match[2] ?? null,
                'default' => $match[3] ?? null,
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

        if (str_contains($route, '{:') === false) {
            $this->methodPath = trim(trim(strtok($route, '{')), '/');
        }
        if (str_contains($route, '{:') === true) {
            $this->methodPath = trim(trim(substr($route, 0, strpos($route, '}') + 1)), '/');
        }

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
     * @throws \Exception
     */
    public function typeConversion(?string $type, mixed $param): mixed
    {
        return match ($type) {
            'integer' => filter_var($param, FILTER_VALIDATE_INT) === false ? throw new BadRequestHttpException('Параметр должен быть типа int') : $param,
            'boolean' => filter_var($param, FILTER_VALIDATE_BOOL) === false ? throw new BadRequestHttpException('Параметр должен быть типа bool') : $param,
            'float' => filter_var($param, FILTER_VALIDATE_FLOAT) === false ? throw new BadRequestHttpException('Параметр должен быть типа float') : $param,
            default => throw new BadRequestHttpException('Передан неподдерживаемый тип')
        };
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
     * @throws \Exception
     */
    private function mapParams(array $queryParams, array $params): array
    {
        $result = [];

        foreach ($params as $param) {
            if (isset($queryParams[$param['name']]) === true) {
                $result[] = $this->typeConversion($param['type'], $queryParams[$param['name']]);

                continue;
            }
            if (isset($param['default']) === true) {
                $result[] = $this->typeConversion($param['type'], $param['default']);

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
        $params = null;

        try {
            if (isset($this->routes[$httpMethod][$requestPath]) === false) {
                foreach ($this->routes[$httpMethod] as $path => $route) {
                    if ($this->isDynamicRoute($path, $requestPath, $params)) {
                        break;
                    }
                }

                if (empty($params) === true) {
                    throw new NotFoundHttpException('Страница не найдена', 404);
                }
            }

            $this->setMiddlewareHandler();

            $route = $route ?? $this->routes[$httpMethod][$requestPath];

            $this->applyMiddlewares($route);

            $params = $params ?? $this->mapParams($request->getQueryParams(), $route->params);

            // вызов обработчика с передачей параметров из запроса
            $controller = $this->container->get($route->handler);
            $action = $route->action;

            return $controller->$action(...$params);
        } catch (Throwable $error) {
            $this->invokeErrorMiddleware($route->errorMiddleware, $error);

            throw $error;
        }
    }

    /**
     * @param string $routePath
     * @param string $requestPath
     * @param array|null $params
     * @return bool
     * @throws \Exception
     */
    private function isDynamicRoute(string $routePath, string $requestPath, ?array &$params): bool
    {
        if (str_contains($routePath, '{:')) {
            $routePrefix = trim(strtok($routePath, '{:'), '/');
            $paramType = substr($routePath, strpos($routePath, '|') + 1, strpos($routePath, '}') - strpos($routePath, '|') - 1);
            $requestPrefix = trim(substr($requestPath, 0, strrpos($requestPath, '/')), '/');

            if ($requestPrefix === $routePrefix) {
                $paramValue = substr($requestPath, strrpos($requestPath, '/') + 1);
                $params[] = $this->typeConversion($paramType, $paramValue);

                return true;
            }
        }

        return false;
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
        $this->executeMiddlewares($route->middlewares);

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

    private function setMiddlewareHandler():void
    {
        $groups = $this->routes['groups'] ?? null;

        if (isset($groups) === false) {
            return;
        }

        unset($this->routes['groups']);

        foreach ($groups as $group) {
            foreach ($this->routes as $method => $routes) {
                $this->addMiddlewareFromGroupHandler($method, $routes, $group);
            }
        }
    }

    private function addMiddlewareFromGroupHandler(string $method, array $routes, Route $group):void
    {
        foreach ($routes as $route => $value) {
            if (str_contains($route, $group->path)) {
                $middlewares =& $this->routes[$method][$route]->middlewares;
                $middlewares = array_merge($middlewares, $group->middlewares);

                $errorMiddlewares =& $this->routes[$method][$route]->errorMiddleware;
                $errorMiddlewares = array_merge($errorMiddlewares, $group->errorMiddleware);
            }
        }
    }
}
