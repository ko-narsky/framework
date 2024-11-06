<?php

namespace Konarsky\middleware;

use Konarsky\contracts\ErrorHandlerInterface;
use Konarsky\contracts\ErrorMiddlewareInterface;
use Konarsky\http\enum\ContentTypes;
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
