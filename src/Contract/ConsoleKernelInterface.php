<?php

namespace Konarsky\Contract;

interface ConsoleKernelInterface
{
    /**
     * Возврат имени приложения
     *
     * @return string
     */
    public function getAppName(): string;

    /**
     * Возврат версии приложения
     *
     * @return string
     */
    public function getVersion(): string;

    /**
     * Возврат карты команд
     *
     * @return array
     */
    public function getCommands(): array;

    /**
     * Регистрация неймспейсов команд
     *
     * @return void
     */
    public function registerCommandNamespaces(array $commandNameSpaces): void;

    /**
     * Обработка запроса
     *
     * @return int
     */
    public function handle(): int;

    public function terminate(int $status): never;
}
