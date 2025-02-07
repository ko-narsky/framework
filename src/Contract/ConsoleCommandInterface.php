<?php

namespace Konarsky\Contract;

interface ConsoleCommandInterface
{
    /**
     * Логика команды
     *
     * @return void
     */
    public function execute(): void;
}