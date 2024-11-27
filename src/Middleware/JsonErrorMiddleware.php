<?php

namespace Konarsky\Middleware;

use Konarsky\Contracts\ErrorHandlerInterface;
use Konarsky\Contracts\ErrorMiddlewareInterface;
use Konarsky\HTTP\Enums\ContentTypes;
use Throwable;

readonly class JsonErrorMiddleware implements ErrorMiddlewareInterface
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler,
    ) {
    }

    public function __invoke(Throwable $e): void
    {
        $this->errorHandler->setContentType(ContentTypes::APPLICATION_JSON);
    }
}
