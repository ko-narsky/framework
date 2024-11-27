<?php

namespace Konarsky\Contracts;

interface LoggerInterface
{
    /**
     * Логирование критической ошибки
     *
     * @param mixed $message сообщение
     *
     * @param string|null $category
     * @return void
     */
    public function critical(string $message, string|null $category = null): void;

    /**
     * Логирование ошибки
     *
     * @param mixed $message сообщение
     * @param string|null $category
     * @return void
     */
    public function error(string $message, string|null $category = null): void;

    /**
     * Логирование предупредительного сообщения
     *
     * @param mixed $message сообщение
     * @param string|null $category
     * @return void
     */
    public function warning(string $message, string|null $category = null): void;

    /**
     * Логирование информационного сообщения
     *
     * @param mixed $message сообщение
     * @param string|null $category
     * @return void
     */
    public function info(string $message, string|null $category = null): void;

    /**
     * Логирование сообщения отладки
     *
     * @param mixed $message сообщение
     * @param string|null $category
     * @return void
     */
    public function debug(string $message, string|null $category = null): void;
}