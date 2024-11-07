<?php

namespace Konarsky\contracts;

interface ViewRendererInterface
{
    /**
     * @param string $view
     * @param array $params
     * @param string|null $viewRootDirectory
     *
     * @return string
     */
    public function render(string $view, array $params, string|null $viewRootDirectory = null): string;

    /**
     * @param string $directoryAlias
     *
     * @return void
     */
    public function setDefaultDirectory(string $directoryAlias): void;
}
