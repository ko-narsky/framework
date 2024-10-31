<?php

declare(strict_types=1);

namespace Konarsky\http;

use Konarsky\contracts\ErrorHandlerInterface;
use Konarsky\contracts\HttpKernelInterface;
use Konarsky\contracts\HTTPRouterInterface;
use Konarsky\contracts\LoggerInterface;
use Konarsky\http\exception\HttpException;
use Konarsky\http\response\CreateResponse;
use Konarsky\http\response\DeleteResponse;
use Konarsky\http\response\HtmlResponse;
use Konarsky\http\response\JsonResponse;
use Konarsky\http\response\UpdateResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class HttpKernel implements HttpKernelInterface
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

            if ($result instanceof HtmlResponse) {
                $this->response = $this->response->withHeader('Content-Type', 'text/html')
                    ->withBody(new Stream($result->body));
            }

            if ($result instanceof JsonResponse) {
                $body = json_encode($result->body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

                if ((bool)$body === false) {
                    throw new HttpException('Ошибка при формировании JSON', 500);
                }

                $this->response = $this->response->withHeader('Content-Type', 'application/json')
                    ->withBody(new Stream($body));
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
            $this->response = $this->response->withStatus($e->getStatusCode());

            $this->logger->error($e->getMessage(), 'Ядро HTTP');

            $this->response = $this->response->withBody(new Stream($this->errorHandler->handle($e)));
        } catch (Throwable $e) {
            $this->response = $this->response->withStatus(500);

            $this->logger->error($e->getMessage(), 'Ядро HTTP');

            $this->response = $this->response->withBody(new Stream($this->errorHandler->handle($e)));
        } finally {
            return $this->response;
        }
    }
}
