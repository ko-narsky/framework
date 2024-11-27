<?php

namespace Konarsky\Console;

use Konarsky\Console\Commands\ListCommand;
use Konarsky\Contracts\{
    ConsoleCommandInterface,
    ConsoleInputInterface,
    ConsoleOutputInterface,
    ConsoleKernelInterface,
    ErrorHandlerInterface,
    LoggerInterface
};
use Psr\Container\ContainerInterface;
use ReflectionClass;

final class ConsoleKernel implements ConsoleKernelInterface
{
    private string $defaultCommand = 'list';

    private array $commandMap = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly LoggerInterface $logger,
        private readonly ErrorHandlerInterface $errorHandler,
        private readonly string $appName,
        private readonly string $version,
    ) {
        $this->initDefaultCommands();
    }

    /**
     * @inheritDoc
     */
    public function getAppName(): string
    {
        return $this->appName;
    }

    /**
     * @inheritDoc
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritDoc
     */
    public function getCommands(): array
    {
        return $this->commandMap;
    }

    /**
     * @inheritDoc
     */
    public function registerCommandNamespaces(array $commandNameSpaces): void
    {
        foreach ($commandNameSpaces as $namespace) {
            $this->registerCommandNamespace($namespace);
        }
    }

    /**
     * Регистрация класса команды
     *
     * @param string $className
     * @return void
     * @throws \ReflectionException
     */
    private function registerCommand(string $className): void
    {
        if (is_subclass_of($className, ConsoleCommandInterface::class) === false) {
            throw new \RuntimeException("Класс $className должен реализовывать интерфейс ConsoleCommandInterface");
        }

        $reflectedClass = new ReflectionClass($className);

        $commandDefinition = new CommandDefinition(
            $reflectedClass->getProperty('signature')->getValue(),
            $reflectedClass->getProperty('description')->getValue()
        );

        $this->commandMap[$commandDefinition->getCommandName()] = $className;
    }

    /**
     * Регистрация неймспейса команды
     *
     * @return void
     * @throws \Exception
     */
    private function registerCommandNamespace(string $commandNameSpace): void
    {
        $paths = glob($commandNameSpace . '/*.php');

        foreach ($paths as $path) {
            if ((bool) preg_match('/namespace\s+([^;]+);/', file_get_contents($path), $matches) === false) {
                continue;
            }

            $namespace = $matches[1] . '\\' .  basename($path, '.php');

            if (class_exists($namespace)) {
                $this->registerCommand($namespace);
            }
        }
    }

    /**
     * Регистрация команд по-умолчанию
     *
     * @return void
     * @throws \ReflectionException
     */
    private function initDefaultCommands(): void
    {
        $defaultCommands = [
            ListCommand::class,
        ];

        foreach ($defaultCommands as $className) {
            $this->registerCommand($className);
        }
    }

    /**
     * @inheritDoc
     */
    public function handle(): int
    {
        try {
            $commandName = $this->input->getFirstArgument() ?? $this->defaultCommand;

            $commandName = $this->commandMap[$commandName]
                ?? throw new \InvalidArgumentException(sprintf("Команда %s не найдена", $commandName));

            $this->container
                ->build($commandName)
                ->execute($this->input, $this->output);
        } catch (\Throwable $e) {

            $message = $this->errorHandler->handle($e);

            $this->output->stdout($message);

            $this->logger->error($e, 'категория');

            return 1;
        }

        return 0;
    }

    public function terminate(int $status): never
    {
        exit($status);
    }
}
