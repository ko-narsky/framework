<?php

namespace Konarsky\contracts;

interface ViewRendererInterface
{
    /**
     * Рендер страницы
     * Пример вызова:
     * Рендер страницы из файла проекта /view/site/about.php
     * (new View())->render('about', compact('administratorName', 'companyPhone'))
     *
     * @return string
     */
    public function render(): string;
}
