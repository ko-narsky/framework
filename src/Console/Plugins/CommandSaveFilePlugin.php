<?php

namespace Konarsky\Console\Plugins;

use DateTime;
use Konarsky\Console\Enums\ConsoleEvent;
use Konarsky\Contracts\{ConsoleInputInterface,
    ConsoleInputPluginInterface,
    ConsoleOutputInterface,
    EventDispatcherInterface,
    ObserverInterface};
use Konarsky\EventDispatcher\Message;

final class CommandSaveFilePlugin implements ConsoleInputPluginInterface, ObserverInterface
{
    private string $optionName;

    public function __construct(
        private readonly ConsoleInputInterface $input,
        private readonly ConsoleOutputInterface $output,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        $this->optionName = 'save-file';
        $this->input->addDefaultOption($this->optionName, 'Сохранить вывод команды в файл');

        $this->eventDispatcher->attach(ConsoleEvent::CONSOLE_INPUT_AFTER_PARSE->name, $this);
    }

    public function observe(Message $message): void
    {
        if ($this->input->hasOption($this->optionName) === false) {
            return;
        }

        if ($this->input->getOption($this->optionName) === null) {
            $time = DateTime::createFromFormat('U.u', microtime(true))->format("Y-m-d-H-i-s-u");
            $this->output->setStdOut('./runtime/' . $time . '.log');

            return;
        }

        $this->output->setStdOut($this->input->getOption($this->optionName)[0]);
    }
}
