<?php

namespace Konarsky\EventDispatcher;

use InvalidArgumentException;
use Konarsky\Contract\{
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

    public function attach(string $eventName, callable|array|ObserverInterface $observer): void
    {
        if (is_callable($observer) === false && ($observer instanceof ObserverInterface) === false) {
            throw new InvalidArgumentException('Наблюдатель должен быть колбеком или реализовать ObserverInterface');
        }

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
            if (is_callable($observer) === true) {
                $observer($message);
                continue;
            }

            $observer->observe($message);
        }
    }
}
