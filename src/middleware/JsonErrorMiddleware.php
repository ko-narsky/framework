<?php

namespace Konarsky\middleware;

use Konarsky\contracts\ErrorHandlerInterface;
use Konarsky\http\enum\ContentTypes;

class JsonErrorMiddleware
{
    public function __construct(
        private readonly ErrorHandlerInterface $errorHandler,
    ) {
    }

    public function __invoke(): void
    {
        $this->errorHandler->setContentType(ContentTypes::APPLICATION_JSON->value);
    }
}
