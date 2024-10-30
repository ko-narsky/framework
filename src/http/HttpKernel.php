<?php

namespace Konarsky\http;

use Konarsky\contracts\ErrorHandlerInterface;
use Konarsky\contracts\HttpKernelInterface;
use Konarsky\contracts\HTTPRouterInterface;
use Konarsky\contracts\LoggerInterface;
use Konarsky\contracts\ViewRendererInterface;
use Konarsky\http\errorHandler\HttpException;
use Konarsky\http\response\CreateResponse;
use Konarsky\http\response\DeleteResponse;
use Konarsky\http\response\JsonResponse;
use Konarsky\http\response\UpdateResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpKernel implements HttpKernelInterface
{
    public function __construct(
        private ResponseInterface $response,
        private readonly HTTPRouterInterface $router,
        private readonly LoggerInterface $logger,
        private readonly ErrorHandlerInterface $errorHandler,
    ) {
        $this->response = $this->response->withStatus(200);
    }

    public function handle(RequestInterface $request): ResponseInterface
    {
        try {

            $result = $this->router->dispatch($request);

            if ($result instanceof ViewRendererInterface) {
                $this->response = $this->response->withHeader('Content-Type', 'text/html');
                $this->response = $this->response->withBody(new Stream($result->render()));
            }

            if ($result instanceof JsonResponse) {
                $this->response = $this->response->withHeader('Content-Type', 'application/json');
                $this->response = $this->response->withBody(new Stream($result->body));
            }

            if ($result instanceof CreateResponse) {
                $this->response = $this->response->withStatus(201);
            }

            if ($result instanceof DeleteResponse) {
                $this->response = $this->response->withStatus(204);
            }

            if ($result instanceof UpdateResponse) {
                $this->response = $this->response->withStatus(200);
            }

        } catch (HttpException $e) {
            $this->response = $this->response->withStatus($e->getCode(), $e->getMessage());
            // TODO дебаг тег добавить

            $this->logger->error($e, 'Ядро HTTP');

            $this->response = $this->response->withBody(new Stream($this->errorHandler->handle($e)));

        } catch (Throwable $e) {
            $this->response = $this->response->withStatus(500, 'Ошибка на стороне сервера');

            $this->logger->error($e, 'Ядро HTTP');

            $this->response = $this->response->withBody(new Stream($this->errorHandler->handle($e)));
        } finally {

            return $this->response;
        }
    }
}
