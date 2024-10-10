<?php

namespace Konarsky\eventDispatcher;

final readonly class Message
{
    public function __construct(public mixed $message)
    {
    }
}
