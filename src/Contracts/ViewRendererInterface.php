<?php

namespace Konarsky\Contracts;

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
     * @param string $file
     * @param array $params
     *
     * @return string
     */
    public function renderFromFile(string $file, array $params = []): string;

    /**
     * @param string $directoryAlias
     *
     * @return void
     */
    public function setDefaultDirectory(string $directoryAlias): void;
}
