<?php

namespace Konarsky\Exception\Resource;

use Konarsky\Exception\Resource\ResourceException;

class BadRequestResourceException extends ResourceException
{
    public function __construct() {
        parent::__construct(json_encode([
            'cause' => 'Контракт не соответствует требованиям',
            'type' => 'BadRequest'
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }

    public function getStatusCode() : int
    {
        return 400;
    }
}