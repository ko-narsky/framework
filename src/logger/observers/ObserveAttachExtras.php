<?php

namespace Konarsky\logger\observers;

use Konarsky\contracts\ObserverInterface;
use Konarsky\eventDispatcher\Message;
use Konarsky\logger\LogStorageDto;

class ObserveAttachExtras implements ObserverInterface
{
    public function __construct(private LogStorageDto $storage) { }


    public function observe(Message $message): void
    {
        $this->storage->extras = json_encode($message->message, JSON_UNESCAPED_UNICODE);
    }
}
