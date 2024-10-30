<?php

namespace Konarsky\http;

use Konarsky\contracts\ErrorHandlerInterface;
use Konarsky\http\errorHandler\HttpNotFoundException;
use Throwable;

readonly class ErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private string $env,
        private bool $debug,
        private string $debugTag
    ) {
    }

    public function handle(Throwable $e): string
    {
        ob_start();

        if ($e instanceof HttpNotFoundException) {
            $statusCode = $e->getStatusCode();
            $message = $e->getMessage();

            include __DIR__ . '/../errorHandler/views/error400.php';

            return ob_get_clean();
        }

        include __DIR__ . '/../errorHandler/views/error500.php';

        return ob_get_clean();
    }
}
