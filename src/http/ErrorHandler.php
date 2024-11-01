<?php

declare(strict_types=1);

namespace Konarsky\http;

use Konarsky\contracts\DebugTagStorageInterface;
use Konarsky\contracts\ErrorHandlerInterface;
use Throwable;

class ErrorHandler implements ErrorHandlerInterface
{
    public bool $asJson = false;
    public function __construct(
        private readonly DebugTagStorageInterface $debugTagStorage,
        private readonly bool $debug,
    ) {
    }

    public function handle(Throwable $e, bool $asJson = true): string
    {
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $message = $e->getMessage();
        $trace = $e->getTraceAsString();
        $debug = $this->debug;
        $debugTag = $this->debugTagStorage->getTag();

        if ($this->asJson === true) {

            return json_encode([
                'message' => $message,
                'x-debug-tag' => $debugTag,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        ob_start();
        include __DIR__ . '/../errorHandler/views/error.php';
        return ob_get_clean();
    }

    public function asJson(): void
    {
        $this->asJson = true;
    }

    public function asHtml(): void
    {
        $this->asJson = false;
    }
}
