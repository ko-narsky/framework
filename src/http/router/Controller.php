<?php

declare(strict_types=1);

namespace Konarsky\http\router;

use Konarsky\contracts\ViewRendererInterface;
use Konarsky\http\response\HtmlResponse;

class Controller
{
    public function __construct(
        private readonly ViewRendererInterface $viewRenderer,
    ) {
    }

    protected function render(string $view, array $params = []): HtmlResponse
    {
        return new HtmlResponse($this->viewRenderer->render($view, $params, $this));
    }
}