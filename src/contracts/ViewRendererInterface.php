<?php

namespace Konarsky\contracts;

interface ViewRendererInterface
{
    /**
     * @param string $view
     * @param array $params
     * @param object $context
     *
     * @return string
     */
    public function render(string $view, array $params, object $context): string;
}
