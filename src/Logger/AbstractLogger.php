<?php

namespace Konarsky\Logger;

use Konarsky\Contract\LoggerInterface;
use Konarsky\Logger\Enum\LogLevel;

abstract class AbstractLogger implements LoggerInterface
{
    /**
     * @inheritDoc
     */
    public function critical(string $message, string|null $category = null): void
    {
        $this->log(LogLevel::CRITICAL->value, [$message, $category]);
    }

    /**
     * @inheritDoc
     */
    public function error(string $message, string|null $category = null): void
    {
        $this->log(LogLevel::ERROR->value, [$message, $category]);
    }

    /**
     * @inheritDoc
     */
    public function warning(string $message, string|null $category = null): void
    {
        $this->log(LogLevel::WARNING->value, [$message, $category]);
    }

    /**
     * @inheritDoc
     */
    public function info(string $message, string|null $category = null): void
    {
        $this->log(LogLevel::INFO->value, [$message, $category]);
    }

    /**
     * @inheritDoc
     */
    public function debug(string $message, string|null $category = null): void
    {
        $this->log(LogLevel::DEBUG->value, [$message, $category]);
    }

    /**
     * Форматирование строки логирования
     *
     * @param string $level уровень логирования
     * @param mixed $logMessageData
     * @return string
     */
    abstract protected function formatMessage(string $level, mixed $logMessageData): string;

    /**
     * Запись форматированного лога в вывод
     *
     * @param string $log форматированный лог
     * @return void
     */
    abstract protected function writeLog(string $log): void;

    /**
     * Запись лога в вывод
     *
     * @param string $level уровень логирования
     * @param mixed $message сообщение
     * @return void
     */
    private function log(string $level, mixed $message): void
    {
        $log = $this->formatMessage($level, $message);
        $this->writeLog($log);
    }
}
