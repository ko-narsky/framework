<?php

declare(strict_types=1);

namespace Konarsky\HTTP;

use Closure;
use Konarsky\Contract\DebugTagStorageInterface;
use Konarsky\Contract\ErrorHandlerInterface;
use Konarsky\Contract\ViewRendererInterface;
use Konarsky\Exception\Resource\ResourceException;
use Konarsky\HTTP\Enum\ContentTypes;
use Throwable;

class ErrorHandler implements ErrorHandlerInterface
{
    private ContentTypes $contentType = ContentTypes::TEXT_HTML;
    private ?Closure $customHandle = null;

    public function __construct(
        private readonly DebugTagStorageInterface $debugTagStorage,
        private readonly ViewRendererInterface $viewRenderer,
        private readonly bool $debug,
    ) {
    }

    public function handle(Throwable $e): string
    {
        if ($this->customHandle !== null) {
            return ($this->customHandle)($e);
        }

        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $message = $e->getMessage();
        $trace = $e->getTraceAsString();
        $debug = $this->debug;
        $debugTag = $this->debugTagStorage->getTag();

        if ($this->contentType === ContentTypes::APPLICATION_JSON) {

            if ($e instanceof ResourceException) {
                return $e->getMessage();
            }

            return json_encode([
                'message' => $message,
                'x-debug-tag' => $debugTag,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        return $this->viewRenderer->renderFromFile(
            __DIR__ . '/../View/ErrorHandler/error.php',
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

    public function setCustomHandle(Closure $handle): void
    {
        $this->customHandle = $handle;
    }
}
