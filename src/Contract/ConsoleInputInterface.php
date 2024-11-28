<?php

namespace Konarsky\Contract;

use Konarsky\Console\CommandDefinition;

interface ConsoleInputInterface
{
    /**
     * Возврат объекта описания вызванной команды
     *
     * @return CommandDefinition
     */
    public function getDefinition(): CommandDefinition;

    /**
     * Регистрация плагинов
     *
     * @param  array $plugins неймспейсы плагинов
     * @return void
     */
    public function addPlugins(array $plugins): void;

    /**
     * Получение названия вызываемой команды
     *
     * @return string|null
     */
    public function getFirstArgument(): string|null;

    /**
     * Регистрация объекта описания консольного вызова
     *
     * @param ConsoleCommandInterface $command инстанс вызываемой команды
     * @return void
     */
    public function bindDefinitions(ConsoleCommandInterface $command): void;

    /**
     * Установка значения для аргумента
     *
     * @param string $name имя аргумента
     * @param null|string $value значение аргумента
     * @return void
     */
    public function setArgumentValue(string $name, null|string $value): void;

    /**
     * Проверка наличия зарегистрированного аргумента
     *
     * @param string $name имя аргумента
     * @return bool
     */
    public function hasArgument(string $name): bool;

    /**
     * Получить значение аргумента
     *
     * @param string $name имя аргумента
     * @return int|string
     */
    public function getArgument(string $name): int|string;

    /**
     * Регистрация опции по-умолчанию
     *
     * @param string $name имя опции
     * @param string $description описание действия опции
     * @return void
     */
    public function addDefaultOption(string $name, string $description): void;

    /**
     * Проверка наличия установленной опции вызова
     *
     * @param string $name имя опции
     * @return bool
     */
    public function hasOption(string $name): bool;

    /**
     * Получить опции по умолчанию
     *
     * @return array
     */
    public function getDefaultOptions(): array;

    /**
     * Принудительно активировать опцию вызова команды
     *
     * @param string $name имя опции
     * @return void
     */
    public function enableOption(string $name): void;
}
