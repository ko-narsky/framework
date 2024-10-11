<?php

namespace Konarsky\http\errorHandler;

use Konarsky\contracts\ErrorHandlerInterface;

class ErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private readonly string $env,
        private readonly bool   $debug,
        private readonly string $debugTag
    ) { }

    public function handle(\Throwable $e): string
    {
        ob_start();

        if ($e instanceof HttpNotFoundException) {
            $statusCode = $e->getStatusCode();
            $message = $e->getMessage();

            include __DIR__ . '/../../errorHandler/views/error400.php';

            return ob_get_clean();
        }

        include __DIR__ . '/../../errorHandler/views/error500.php';

        return ob_get_clean();
    }
}