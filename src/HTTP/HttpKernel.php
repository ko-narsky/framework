<?php

declare(strict_types=1);

namespace Konarsky\HTTP;

use Konarsky\Contracts\ErrorHandlerInterface;
use Konarsky\Contracts\HttpKernelInterface;
use Konarsky\Contracts\HTTPRouterInterface;
use Konarsky\Contracts\LoggerInterface;
use Konarsky\HTTP\Enums\ContentTypes;
use Konarsky\HTTP\Exceptions\HttpException;
use Konarsky\HTTP\Responses\CreateResponse;
use Konarsky\HTTP\Responses\DeleteResponse;
use Konarsky\HTTP\Responses\HtmlResponse;
use Konarsky\HTTP\Responses\JsonResponse;
use Konarsky\HTTP\Responses\UpdateResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $result = $this->router->dispatch($request);

            if ($result instanceof HtmlResponse) {
                $this->response = $this->response->withHeader('Content-Type', ContentTypes::TEXT_HTML->value)
                    ->withBody(new Stream($result->body));
            }

            if ($result instanceof JsonResponse) {
                $body = json_encode($result->body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

                if ((bool)$body === false) {
                    throw new HttpException('Ошибка при формировании JSON', 500);
                }

                $this->response = $this->response->withHeader('Content-Type', ContentTypes::APPLICATION_JSON->value)
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
            $this->response = $this->response->withHeader('Content-Type', $this->errorHandler->getContentType()->value)
                ->withStatus($e->getStatusCode());

            $this->logger->error($e->getMessage(), 'Ядро HTTP');

            $this->response = $this->response->withBody(new Stream($this->errorHandler->handle($e)));
        } catch (Throwable $e) {
            $this->response = $this->response->withHeader('Content-Type', $this->errorHandler->getContentType()->value)
                ->withStatus(500);

            $this->logger->error($e->getMessage(), 'Ядро HTTP');

            $this->response = $this->response->withBody(new Stream($this->errorHandler->handle($e)));
        } finally {
            return $this->response;
        }
    }
}
