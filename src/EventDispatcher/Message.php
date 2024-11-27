<?php

namespace Konarsky\EventDispatcher;

final readonly class Message
{
    public function __construct(public mixed $message)
    {
    }
}
