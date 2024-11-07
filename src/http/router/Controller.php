<?php

declare(strict_types=1);

namespace Konarsky\http\router;

use Konarsky\contracts\ViewRendererInterface;
use Konarsky\http\response\HtmlResponse;

abstract class Controller
{
    public function __construct(
        protected readonly ViewRendererInterface $viewRenderer,
    ) {
    }

    protected function render(string $view, array $params = [], string|null $viewRootDirectory = null): HtmlResponse
    {
        return new HtmlResponse($this->viewRenderer->render($view, $params, $viewRootDirectory));
    }
}
