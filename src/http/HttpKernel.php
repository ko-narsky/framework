<?php

namespace Konarsky\http;

use Konarsky\contracts\ErrorHandlerInterface;
use Konarsky\contracts\EventDispatcherInterface;
use Konarsky\contracts\HttpKernelInterface;
use Konarsky\contracts\HTTPRouterInterface;
use Konarsky\contracts\LoggerInterface;
use Konarsky\http\errorHandler\HttpNotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

//use Psr\Log\LoggerInterface;

class HttpKernel implements HttpKernelInterface
{
    public function __construct(
        private readonly ContainerInterface       $container,
        private ResponseInterface                 $response,
        private readonly HTTPRouterInterface      $router,
        private readonly LoggerInterface          $logger,
        private readonly ErrorHandlerInterface    $errorHandler,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function handle(RequestInterface $request): ResponseInterface
    {
        try {
            $result = $this->router->dispatch($request);

            // установка типа контента ответа в зависимости от типа, возвращенного в переменную $result
            if ($result instanceof ResponseInterface) {
                $this->response = $result;
            }
            if (is_string($result) === true) {
                $this->response->getBody()->write($result);
            }
        } catch (HttpNotFoundException $e) {
            // установка статус-кода в объект ответа
            // установка строки reason phrase в объект ответа
            $this->response = $this->response
                ->withStatus($e->getCode(), $e->getMessage());

            // делегирование логирования ошибки объекту LoggerInterface
            $this->logger->error($e, 'категория');

            // делегирование обработки ошибки объекту интерфейса ErrorHandlerInterface
            // установка тела в объект ответа
            $this->response->getBody()->write($this->errorHandler->handle($e));
        } catch (Throwable $e) {
            // установка статус-кода 500 в объект ответа
            // установка строки reason phrase в объект ответа о внутренней ошибки сервера
            $this->response = $this->response
                ->withStatus(500, 'Ошибка на стороне сервера');

            // делегирование логирования ошибки объекту LoggerInterface
            $this->logger->error($e, 'категория');

            // делегирование обработки ошибки объекту интерфейса ErrorHandlerInterface
            // установка тела в объект ответа
            $this->response->getBody()->write($this->errorHandler->handle($e));
        } finally {
            // установка типа контента ответа в соответствием с типом контента запроса
            $this->response = $this->response->withHeader('Content-Type', $this->determineContentType($request));
        }

        return $this->response;
    }

    private function determineContentType($request): string
    {
        // Пример определения типа контента на основе результата или запроса
        if ($request instanceof ResponseInterface) {
            return $request->getHeaderLine('Content-Type');
        } elseif ($request instanceof RequestInterface) {
            return $request->getHeaderLine('Accept') ?: 'text/html';
        }

        return 'text/plain';
    }
}
