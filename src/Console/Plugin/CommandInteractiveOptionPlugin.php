<?php

namespace Konarsky\Console\Plugin;

use Konarsky\Console\Enum\ConsoleEvent;
use Konarsky\Contract\{
    ConsoleInputInterface,
    ConsoleInputPluginInterface,
    ConsoleOutputInterface,
    EventDispatcherInterface,
    ObserverInterface
};
use Konarsky\EventDispatcher\Message;

final class CommandInteractiveOptionPlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private string $optionName;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        $this->optionName = 'interactive';
        $this->input->addDefaultOption($this->optionName, 'Вызов команды в режиме интерактивного ввода');

        $this->eventDispatcher->attach(ConsoleEvent::CONSOLE_INPUT_AFTER_PARSE->name, $this);
    }
    public function observe(Message $message): void
    {
        if ($this->input->hasOption($this->optionName) === false) {
            return;
        }

        $commandDefinition = $this->input->getDefinition();

        foreach ($commandDefinition->getArguments() as $argument) {
            $outputText = "Введите аргумент $argument (";
            $outputText .= $commandDefinition->getArgumentDefinition($argument)->description;
            $outputText .= ')';

            $defaultValue = $commandDefinition->getArgumentDefinition($argument)->defaultValue;
            if (is_null($defaultValue) === false) {
                $outputText .= " [$defaultValue]";
            }
            $outputText .= ':';

            $this->output->success($outputText);
            $this->output->writeLn();

            $value = trim(fgets(STDIN));
            $this->input->setArgumentValue($argument, $value);
        }
    }
}
