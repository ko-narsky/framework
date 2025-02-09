<?php

declare(strict_types=1);

namespace Konarsky\HTTP;

use Konarsky\Contract\ErrorHandlerInterface;
use Konarsky\Contract\HttpKernelInterface;
use Konarsky\Contract\HTTPRouterInterface;
use Konarsky\Contract\LoggerInterface;
use Konarsky\Exception\HTTP\HttpException;
use Konarsky\Exception\Resource\ResourceException;
use Konarsky\HTTP\Enum\ContentTypes;
use Konarsky\HTTP\Response\CreateResponse;
use Konarsky\HTTP\Response\DeleteResponse;
use Konarsky\HTTP\Response\HtmlResponse;
use Konarsky\HTTP\Response\JsonResponse;
use Konarsky\HTTP\Response\PatchResponse;
use Konarsky\HTTP\Response\UpdateResponse;
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
        $this->response = $this->response->withBody(new Stream(''));
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

            if ($result instanceof PatchResponse) {
                $this->response = $this->response->withStatus(200);
            }
        } catch (HttpException|ResourceException $e) {
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
