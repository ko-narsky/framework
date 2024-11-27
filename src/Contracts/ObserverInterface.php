<?php

namespace Konarsky\Contracts;

use Konarsky\EventDispatcher\Message;

interface ObserverInterface
{
    public function observe(Message $message): void;
}