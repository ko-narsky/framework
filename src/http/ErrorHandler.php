<?php

declare(strict_types=1);

namespace Konarsky\http;

use Konarsky\contracts\DebugTagStorageInterface;
use Konarsky\contracts\ErrorHandlerInterface;
use Konarsky\http\enum\ContentTypes;
use Throwable;

class ErrorHandler implements ErrorHandlerInterface
{
    private ContentTypes $contentType = ContentTypes::TEXT_HTML;
    public function __construct(
        private readonly DebugTagStorageInterface $debugTagStorage,
        private readonly bool $debug,
    ) {
    }

    public function handle(Throwable $e): string
    {
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $message = $e->getMessage();
        $trace = $e->getTraceAsString();
        $debug = $this->debug;
        $debugTag = $this->debugTagStorage->getTag();

        if ($this->contentType === ContentTypes::APPLICATION_JSON) {

            return json_encode([
                'message' => $message,
                'x-debug-tag' => $debugTag,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        ob_start();
        include __DIR__ . '/../errorHandler/views/error.php';
        return ob_get_clean();
    }

    public function setContentType(ContentTypes $contentType): void
    {
        $this->contentType = $contentType;
    }

    public function getContentType(): ContentTypes
    {
        return $this->contentType;
    }
}
