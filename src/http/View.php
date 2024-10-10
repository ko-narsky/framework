<?php

namespace Konarsky\http;

use Konarsky\contracts\ViewRendererInterface;

class View implements ViewRendererInterface
{
    public function __construct(private string $path = __DIR__ . '/../../views/') { }

    /**
     * Рендер страницы
     * Пример вызова:
     * Рендер страницы из файла проекта /view/site/about.php
     * (new View())->render('about', compact('administratorName', 'companyPhone'))
     *
     * @param string $view имя вью файла отрисовки страницы
     * @param array $params значения переменных, используемых для отрисовки представления
     * @return void
     */
    public function render(string $view, array $params, object $context): void
    {
        $context = explode('\\', get_class($context));

        if ($context[0] === 'Modules') {
            $this->path = __DIR__ . '/../../modules/views/';
        }

        $entrypoint = strtolower(str_replace('Controller', '', array_pop($context)));

        extract($params);

        $file = $this->path . $entrypoint . '/' . $view . '.php';

        if (file_exists($file) === false) {
            throw new ViewNotFoundException('Нет такого файла');
        }

        require($file);
    }
}