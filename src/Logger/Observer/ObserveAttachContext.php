<?php

namespace Konarsky\Logger\Observer;

use Konarsky\Contract\ObserverInterface;
use Konarsky\EventDispatcher\Message;
use Konarsky\Logger\LogStorageDto;

class ObserveAttachContext implements ObserverInterface
{
    public function __construct(private LogStorageDto $storage) { }

    public function observe(Message $message): void
    {
        $this->storage->context[$message->message] = $message->message;
    }
}
