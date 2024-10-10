<?php

namespace Konarsky\logger\observers;

use Konarsky\contracts\ObserverInterface;
use Konarsky\eventDispatcher\Message;
use Konarsky\logger\LogStorageDto;

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
