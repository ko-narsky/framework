<?php

namespace Konarsky\Contract;

use Konarsky\HTTP\Router\Route;
use Psr\Http\Message\RequestInterface;

interface HTTPRouterInterface
{
    /**
     * Регистрация глобального мидлвеера
     *
     * @param string|callable $middleware коллбек функция или неймспейс класса мидлвеера
     * @return HTTPRouterInterface
     */
    public function addGlobalMiddleware(callable|string $middleware): HTTPRouterInterface;

    /**
     * Добавление маршрута для метода GET
     *
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция или неймспейс класса
     * @return Route
     */
    public function get(string $route, string|callable $handler): Route;

    /**
     * Добавление маршрута для метода POST
     *
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция или неймспейс класса
     * @return Route
     */
    public function post(string $route, string|callable $handler): Route;

    /**
     * Добавление маршрута для метода PUT
     *
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция или неймспейс класса
     * @return Route
     */
    public function put(string $route, string|callable $handler): Route;

    /**
     * Добавление маршрута для метода PATCH
     *
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция или неймспейс класса
     * @return Route
     */
    public function patch(string $route, string|callable $handler): Route;

    /**
     * Добавление маршрута для метода DELETE
     *
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция или неймспейс класса
     * @return Route
     */
    public function delete(string $route, string|callable $handler): Route;

    /**
     * Группировка маршрутов
     *
     * @param string $name имя группы
     * @param callable $handler функция, которая настраивает маршруты группы
     * @return Route
     */
    public function group(string $name, callable $handler): Route;

    /**
     * Добавление маршрута для метода запроса
     *
     * @param string $method метод запроса
     * @param string $route путь
     * @param string|callable $handler обработчик - коллбек функция
     * или неймспейс класса в формате 'Неймспейс::метод'
     * @return Route
     */
    public function add(string $method, string $route, string|callable $handler): Route;

    /**
     * Диспетчеризация входящего запроса
     *
     * @param RequestInterface $request объект запроса
     * @return mixed
     */
    public function dispatch(RequestInterface $request): mixed;
}
