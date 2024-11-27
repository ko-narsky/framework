<?php

namespace Konarsky\Logger\Observers;

use Konarsky\Contracts\ObserverInterface;
use Konarsky\EventDispatcher\Message;
use Konarsky\Logger\LogStorageDto;

class ObserveDetachContext implements ObserverInterface
{
    public function __construct(private LogStorageDto $storage) { }

    public function observe(Message $message): void
    {
        if (isset($this->storage->context[$message->message]) === false) {
            return;
        }

        unset($this->storage->context[$message->message]);
    }
}
