<?php

namespace Konarsky\Logger\Observers;

use Konarsky\Contracts\ObserverInterface;
use Konarsky\EventDispatcher\Message;
use Konarsky\Logger\LogStorageDto;

class ObserveFlushContext implements ObserverInterface
{
    public function __construct(private LogStorageDto $storage) { }

    public function observe(Message $message): void
    {
        $this->storage->context = [];
    }
}
