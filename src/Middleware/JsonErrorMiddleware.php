<?php

namespace Konarsky\Middleware;

use Konarsky\Contract\ErrorHandlerInterface;
use Konarsky\Contract\ErrorMiddlewareInterface;
use Konarsky\HTTP\Enum\ContentTypes;
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
