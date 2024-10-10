<?php

namespace Konarsky\http;

class Controller
{
    public function render(string $view, array $params = []) {
        (new View())->render($view, $params, $this);
    }
}