<?php

namespace Konarsky\contracts;

interface ViewRendererInterface
{
    /**
     * @param string $view
     * @param array $params
     *
     * @return string
     */
    public function render(string $view, array $params): string;

    /**
     * @param string $directoryAlias
     *
     * @return void
     */
    public function setDefaultDirectory(string $directoryAlias): void;
}
