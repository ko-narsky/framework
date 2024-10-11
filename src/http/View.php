<?php

namespace Konarsky\http;

use Konarsky\contracts\ViewRendererInterface;

class View implements ViewRendererInterface
{
    /**
     * Рендер страницы
     * Пример вызова:
     * Рендер страницы из файла проекта /view/site/about.php
     * (new View())->render('about', compact('administratorName', 'companyPhone'))
     *
     * @param string $view имя вью файла отрисовки страницы
     * @param array $params значения переменных, используемых для отрисовки представления
     * @param object $context Контекст, из которого извлекается информация о контроллере
     * @return void
     * @throws ViewNotFoundException
     */
    public function render(string $view, array $params, object $context): void
    {
        $reflection = new \ReflectionClass($context);

        $path = dirname(dirname($reflection->getFileName()));

        $contextParts = explode('\\',get_class($context));

        $entrypoint = strtolower(str_replace('Controller', '', array_pop($contextParts)));

        $file = $path
            . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . $entrypoint
            . DIRECTORY_SEPARATOR . $view . '.php';

        if (!file_exists($file)) {
            throw new ViewNotFoundException('Нет такого файла: ' . $file);
        }

        extract($params);

        require $file;
    }
}
