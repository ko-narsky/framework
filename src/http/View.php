<?php

namespace Konarsky\http;

use Konarsky\contracts\ViewRendererInterface;
use Konarsky\exception\viewRenderer\ViewNotFoundException;
use Konarsky\http\exception\NotFoundHttpException;

class View implements ViewRendererInterface
{
    private string|null $viewRootDirectory = null;
    private string|null $extension = '.php';

    public function __construct(
        private readonly array $config = []
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws NotFoundHttpException
     */
    public function render(string $view, array $params): string
    {
        $directory = array_values($this->config)[0];

        if ($this->viewRootDirectory !== null) {
            $directory = $this->config[$this->viewRootDirectory];
        }

        $file = $directory . DIRECTORY_SEPARATOR . $view . $this->extension;

        if (file_exists($file) === false) {
            throw new ViewNotFoundException('Файл view не найден');
        }

        extract($params);

        ob_start();

        include $file;

        return ob_get_clean();
    }

    /**
     * @inheritDoc
     *
     * @throws ViewNotFoundException
     */
    public function renderFromFile(string $file, array $params = []): string
    {
        if (file_exists($file) === false) {
            throw new ViewNotFoundException('Файл ' . $file .' не найден');
        }

        extract($params);

        ob_start();

        include $file;

        return ob_get_clean();
    }

    /**
     * @inheritDoc
     */
    public function setDefaultDirectory(string $directoryAlias): void
    {
        $this->viewRootDirectory = $directoryAlias;
    }

    public function setExtension(?string $extension): void
    {
        $this->extension = $extension;
    }
}
