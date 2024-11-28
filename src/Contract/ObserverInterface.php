<?php

namespace Konarsky\Contract;

use Konarsky\EventDispatcher\Message;

interface ObserverInterface
{
    public function observe(Message $message): void;
}