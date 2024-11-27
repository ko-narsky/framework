<?php

declare(strict_types=1);

namespace Konarsky\HTTP;

use Konarsky\Contracts\DebugTagStorageInterface;
use Konarsky\Contracts\ErrorHandlerInterface;
use Konarsky\Contracts\ViewRendererInterface;
use Konarsky\HTTP\Enums\ContentTypes;
use Throwable;

class ErrorHandler implements ErrorHandlerInterface
{
    private ContentTypes $contentType = ContentTypes::TEXT_HTML;
    public function __construct(
        private readonly DebugTagStorageInterface $debugTagStorage,
        private readonly ViewRendererInterface $viewRenderer,
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

        return $this->viewRenderer->renderFromFile(
            __DIR__ . '/../ErrorHandler/views/error.php',
            compact('statusCode', 'message', 'trace', 'debug', 'debugTag')
        );
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
