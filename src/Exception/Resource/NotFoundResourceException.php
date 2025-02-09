<?php

namespace Konarsky\Exception\Resource;

use Konarsky\Exception\Resource\ResourceException;

class NotFoundResourceException extends ResourceException
{
    public function __construct() {
        parent::__construct(json_encode([
            'cause' => 'Запрашиваемый ресурс не найден',
            'type' => 'NotFound'
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }

    public function getStatusCode() : int
    {
        return 404;
    }
}