<?php

namespace Konarsky\console\plugins;

use Konarsky\console\enum\ConsoleEvent;
use Konarsky\contracts\{
    ConsoleInputInterface,
    ConsoleInputPluginInterface,
    ConsoleKernelInterface,
    ConsoleOutputInterface,
    EventDispatcherInterface,
    ObserverInterface
};
use Konarsky\eventDispatcher\Message;

/**
 * Плагин вывода информации о команде
 */
final class CommandHelpOptionPlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private string $optionName;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ConsoleKernelInterface $kernel,
    ) {
        $this->optionName = 'help';
        $this->input->addDefaultOption($this->optionName, 'Вывод информации о команде');

        $this->eventDispatcher->attach(ConsoleEvent::CONSOLE_INPUT_AFTER_PARSE->name, $this);
    }

    public function observe(Message $message): void
    {
        if ($this->input->hasOption($this->optionName) === false) {
            return;
        }

        $commandDefinition = $this->input->getDefinition();

        $this->output->success('Вызов:');
        $this->output->writeLn();
        $callText = '  ';
        $callText .= $this->input->getFirstArgument();
        foreach ($commandDefinition->getArguments() as $argument) {
            $callText .= " [$argument]";
        }
        $callText .= ' [опции]';
        $this->output->stdout($callText);
        $this->output->writeLn(2);

        $this->output->info('Назначение:');
        $this->output->writeLn();
        $this->output->stdout('  ' . $commandDefinition->getCommandDescription());
        $this->output->writeLn(2);

        if (empty($commandDefinition->getArguments()) === false) {
            $this->output->info('Аргументы:');
            $this->output->writeLn();
            foreach ($commandDefinition->getArguments() as $argument) {
                $this->output->success('  ' . $argument);
                $this->output->stdout(' ' . $commandDefinition->getArgumentDefinition($argument)->description);
                $this->output->writeLn();
            }
            $this->output->writeLn();
        }

        if (empty($this->input->getDefaultOptions()) === false) {
            $this->output->info('Опции:');
            $this->output->writeLn();
            foreach ($this->input->getDefaultOptions() as $key => $option) {
                $this->output->success('  ' . $key);
                $this->output->stdout(' ' . $option['description']);
                $this->output->writeLn();
            }
        }

        if (empty($commandDefinition->getOptions()) === false) {
            foreach ($commandDefinition->getOptions() as $option) {
                $this->output->success('  ' . $option);
                $this->output->stdout(' ' . $commandDefinition->getOptionDefinition($option)->description);
                $this->output->writeLn();
            }

            $this->output->writeLn();
        }

        $this->kernel->terminate(0);
    }
}
