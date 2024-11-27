<?php

declare(strict_types=1);

namespace Konarsky\Middleware;

use Konarsky\Contracts\LoggerInterface;
use Konarsky\Contracts\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;

readonly class RequestLogMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(RequestInterface $request): void
    {
        $this->logger->debug(
            "Выполнено обращение методом {$request->getMethod()} к эндпоинту {$request->getUri()->getPath()}",
            'Отработал ' . RequestLogMiddleware::class
        );
    }
}
