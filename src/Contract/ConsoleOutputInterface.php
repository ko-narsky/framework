<?php

namespace Konarsky\Contract;

interface ConsoleOutputInterface
{
    /**
     * Запись строку вывода в поток вывода
     *
     * @param  string $message сообщение вывода
     * @return void
     */
    public function stdout(string $message): void;

    /**
     * Запись строку вывода в поток вывода ошибок
     *
     * @param  string $message сообщение вывода
     * @return void
     */
    public function stdErr(string $message): void;

    /**
     * Вывод сообщения об успехе операции
     *
     * @param  string $message сообщение вывода
     * @return void
     */
    public function success(string $message): void;

    /**
     * Вывод информационного сообщения об операци
     *
     * @param  string $message сообщение вывода
     * @return void
     */
    public function info(string $message): void;

    /**
     * Вывод предупреждающего сообщения об операци
     *
     * @param  string $message сообщение вывода
     * @return void
     */
    public function warning(string $message): void;

    /**
     * Создание массива строк одинакового контента
     *
     * @param  int $count количество повторений строки
     * @return void
     */
    public function writeLn(int $count = 1): void;

    /**
     * Переопределение ресурса вывода
     *
     * @param  resource $resource ресурс вывода
     * @return void
     */
    public function setStdOut($resource): void;

    /**
     * Переопределение ресурса вывода ошибок
     *
     * @param  resource $resource ресурс вывода
     * @return void
     */
    public function setStdErr($resource): void;

    /**
     * Перевод выполнения команды в фон
     *
     * @param  resource $resource ресурс вывода
     * @return void
     */
    public function detach($resource = '/dev/null'): void;
}
