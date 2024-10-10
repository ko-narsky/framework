<?php

namespace Konarsky\contracts;

interface ConsoleCommandInterface
{
    /**
     * Логика команды
     *
     * @return void
     */
    public function execute(): void;
}