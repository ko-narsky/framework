<?php

namespace Konarsky\contracts;

use Konarsky\eventDispatcher\Message;

interface ObserverInterface
{
    public function observe(Message $message): void;
}