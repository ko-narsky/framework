<?php

namespace Konarsky\Contracts;

interface ConsoleCommandInterface
{
    /**
     * Логика команды
     *
     * @return void
     */
    public function execute(): void;
}