<?php

namespace Konarsky\Console\Plugins;

use Konarsky\Console\Enums\ConsoleEvent;
use Konarsky\Contracts\{
    ConsoleInputInterface,
    ConsoleInputPluginInterface,
    ConsoleOutputInterface,
    EventDispatcherInterface,
    ObserverInterface
};
use Konarsky\EventDispatcher\Message;

final class CommandDetachOptionPlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private string $optionName;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        $this->optionName = 'detach';
        $this->input->addDefaultOption($this->optionName, 'Выполнение команды в фоне');

        $this->eventDispatcher->attach(ConsoleEvent::CONSOLE_INPUT_AFTER_PARSE->name, $this);
    }
    public function observe(Message $message): void
    {
        if ($this->input->hasOption($this->optionName) === false) {
            return;
        }

        $this->output->detach();
    }
}
