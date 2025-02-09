<?php

namespace Konarsky\Exception\Resource;

use Konarsky\Exception\Resource\ResourceException;
use Throwable;

class BadRequestResourceException extends ResourceException
{
    public function __construct(string $message = 'Контракт не соответствует требованиям') {
        parent::__construct(json_encode([
            'cause' => $message,
            'type' => 'BadRequest'
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }

    public function getStatusCode() : int
    {
        return 400;
    }
}