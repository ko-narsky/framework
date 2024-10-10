<?php

namespace Konarsky\eventDispatcher;

use InvalidArgumentException;
use Konarsky\contracts\{
    EventDispatcherInterface,
    ObserverInterface
};

class EventDispatcher implements EventDispatcherInterface
{
    private array $observers = [];

    public function configure(array $config): void
    {
        foreach ($config as [$event, $observer]) {
            if ($observer instanceof ObserverInterface) {
                $this->attach($event, $observer);
                continue;
            }

            throw new InvalidArgumentException('Наблюдатель должен реализовать ObserverInterface');
        }
    }

    public function attach(string $eventName, ObserverInterface $observer): void
    {
        $this->observers[$eventName][] = $observer;
    }

    public function detach(string $eventName): void
    {
        unset($this->observers[$eventName]);
    }

    public function trigger(string $eventName, Message $message): void
    {
        if (isset($this->observers[$eventName]) === false) {
            return;
        }

        foreach ($this->observers[$eventName] as $observer) {
            $observer->observe($message);
        }
    }
}
