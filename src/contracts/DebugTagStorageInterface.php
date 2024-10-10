<?php

namespace Konarsky\contracts;

interface DebugTagStorageInterface
{
    /**
     * Получить значение тега
     *
     * @return string
     */
    public function getTag(): string;

    /**
     * Установить значение тега
     *
     * @param string $tag
     * @return void
     */
    public function setTag(string $tag): void;
}