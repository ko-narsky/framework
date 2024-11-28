<?php

namespace Konarsky\Console\Command;

use Konarsky\Console\CommandDefinition;
use Konarsky\Contract\{
    ConsoleCommandInterface,
    ConsoleInputInterface,
    ConsoleKernelInterface,
    ConsoleOutputInterface
};
use ReflectionClass;

/**
 * Команда вывода информации о консольном ядре
 */
class ListCommand implements ConsoleCommandInterface
{
    private static string $signature = 'list {?commandName:имя команды}';
    private static string $description = 'Вывод информации о доступных командах';
    private bool $hidden = true;
    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleKernelInterface $kernel,
        private readonly ConsoleOutputInterface $output,
    ) {
        $this->input->bindDefinitions($this);
    }

    public function execute(): void
    {
        $this->output->info($this->kernel->getAppName());
        $this->output->info(' ' . $this->kernel->getVersion());
        $this->output->writeLn(2);
        $this->output->warning("Фреймворк создан разработчиками Konarsky.\nЯвляется платформой для изучения базового поведения приложения созданного на PHP.\nФреймворк не является production-ready реализацией и не предназначен для коммерческого использования.");
        $this->output->writeLn(2);

        $this->output->stdout('Доступные опции:');
        $this->output->writeLn();

        foreach ($this->input->getDefaultOptions() as $optionName => $optionValue) {
            $this->output->success('  ' . $optionName);
            $this->output->stdout(' - ' . $optionValue['description']);
            $this->output->writeLn();
        }

        $this->output->success('Вызов:');
        $this->output->writeLn();
        $this->output->stdout('  команда [аргументы] [опции]');
        $this->output->writeLn(2);

        $this->output->stdout('Доступные команды:');
        $this->output->writeLn();

        foreach ($this->kernel->getCommands() as $command) {
            $reflectedClass = new ReflectionClass($command);
            $commandDefinition = new CommandDefinition(
                $reflectedClass->getProperty('signature')->getValue(),
                $reflectedClass->getProperty('description')->getValue()
            );

            $this->output->success('  ' . $commandDefinition->getCommandName());
            $this->output->stdout(' - ' . $commandDefinition->getCommandDescription());
            $this->output->writeLn();
        }
    }
}
