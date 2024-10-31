<?php

namespace Konarsky\http;

use Konarsky\contracts\ViewRendererInterface;
use ReflectionClass;

class View implements ViewRendererInterface
{
    /**
     * @inheritDoc
     * @throws ViewNotFoundException
     */
    public function render(string $view, array $params, object $context): string
    {
        $reflection = new ReflectionClass($context);

        $path = dirname(dirname($reflection->getFileName()));

        $contextParts = explode('\\',get_class($context));

        $entrypoint = strtolower(str_replace('Controller', '', array_pop($contextParts)));

        $file = $path
            . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . $entrypoint
            . DIRECTORY_SEPARATOR . $view . '.php';

        if (file_exists($file) === false) {
            throw new ViewNotFoundException('Нет такого файла: ' . $file);
        }

        extract($params);

        ob_start();
        include $file;

        return ob_get_clean();
    }
}
