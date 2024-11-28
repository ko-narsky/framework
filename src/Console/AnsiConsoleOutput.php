<?php

namespace Konarsky\Console;

use Konarsky\Console\Enum\ConsoleColors;
use Konarsky\Contract\ConsoleKernelInterface;
use Konarsky\Contract\ConsoleOutputInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Обработка вывода в терминал консоли
 */
final class AnsiConsoleOutput implements ConsoleOutputInterface
{
    /**
     * @param resource $stdOut Поток вывода
     * @param resource $stdErr Поток вывода ошибок
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private mixed $stdOut = STDOUT,
        private mixed $stdErr = STDERR,
    ) {
    }

    /**
     * Создать строку вывода в формате ANSI
     *
     * @param  string $message сообщение вывода
     * @param  array $format формат вывода (цвет, стиль)
     * @return string
     */
    private function createAnsiLine(string $message, array $format = []): string
    {
        $code = implode(';', $format);

        return "\033[0m" . ($code !== '' ? "\033[" . $code . 'm' : '') . $message . "\033[0m";
    }

    /**
     * Запись строку вывода в поток вывода
     *
     * @param  string $message сообщение вывода
     * @return void
     */
    public function stdout(string $message): void
    {
        $args = func_get_args();
        array_shift($args);

        $line = $this->createAnsiLine($message, $args);

        fwrite($this->stdOut, $line);
    }

    /**
     * Запись строку вывода в поток вывода ошибок
     *
     * @param  string $message сообщение вывода
     * @return void
     */
    public function stdErr(string $message): void
    {
        $args = func_get_args();
        array_shift($args);

        $line = $this->createAnsiLine($message, $args);

        fwrite($this->stdErr, $line);
    }

    /**
     * Вывод сообщения об успехе операции
     *
     * @param  string $message сообщение вывода
     * @return void
     */
    public function success(string $message): void
    {
        $this->stdout($message, ConsoleColors::FG_GREEN->value);
    }

    /**
     * Вывод информационного сообщения об операци
     *
     * @param  string $message сообщение вывода
     * @return void
     */
    public function info(string $message): void
    {
        $this->stdout($message, ConsoleColors::FG_CYAN->value);
    }

    /**
     * Вывод предупреждающего сообщения об операци
     *
     * @param  string $message сообщение вывода
     * @return void
     */
    public function warning(string $message): void
    {
        $this->stdout($message, ConsoleColors::FG_YELLOW->value);
    }

    /**
     * Создание массива строк одинакового контента
     *
     * @param  int $count количество повторений строки
     * @return void
     */
    public function writeLn(int $count = 1): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->stdout("\n");
        }
    }

    /**
     * Переопределение ресурса вывода
     *
     * @param  resource $resource ресурс вывода
     * @return void
     */
    public function setStdOut($resource): void
    {
        if (is_resource($this->stdOut) === true) {
            fclose($this->stdOut);
        }

        $this->stdOut = fopen($resource, 'ab');
    }

    /**
     * Переопределение ресурса вывода ошибок
     *
     * @param  resource $resource ресурс вывода
     * @return void
     */
    public function setStdErr($resource): void
    {
        if (is_resource($this->stdErr) === true) {
            fclose($this->stdErr);
        }

        $this->stdErr = fopen($resource, 'ab');
    }

    /**
     * Перевод выполнения команды в фон
     *
     * @param  resource $resource ресурс вывода
     * @return void
     */
    public function detach($resource = '/dev/null'): void
    {
        $pid = pcntl_fork();

        if ($pid === -1) {
            throw new RuntimeException('Не удалось выполнить форк процесса.');
        }

        if ($pid > 0) {
            $this->container->get(ConsoleKernelInterface::class)->terminate(0);
        }

        if (posix_setsid() === -1) {
            throw new RuntimeException('Не удалось создать новую сессию для дочернего процесса');
        }

        $this->setStdOut($resource);
    }
}
