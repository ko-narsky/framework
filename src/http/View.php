<?php

namespace Konarsky\http;

use Konarsky\contracts\ViewRendererInterface;

class View implements ViewRendererInterface
{
    public function __construct(
        private readonly string $view,
        private array $params,
        private readonly object $context
    ) {
    }

    /**
     * @inheritDoc
     * @throws ViewNotFoundException
     */
    public function render(): string
    {
        $reflection = new \ReflectionClass($this->context);

        $path = dirname($reflection->getFileName(), 2);

        $contextParts = explode('\\', get_class($this->context));

        $entrypoint = strtolower(str_replace('Controller', '', array_pop($contextParts)));

        $file = $path
            . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . $entrypoint
            . DIRECTORY_SEPARATOR . $this->view . '.php';

        if (file_exists($file) === false) {
            throw new ViewNotFoundException('Нет такого файла: ' . $file);
        }

        extract($this->params);

        ob_start();
        include $file;

        return ob_get_clean();
    }
}
