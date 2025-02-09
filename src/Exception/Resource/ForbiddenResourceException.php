<?php

namespace Konarsky\Exception\Resource;

use Konarsky\Exception\Resource\ResourceException;

class ForbiddenResourceException extends ResourceException
{
    public function __construct() {
        parent::__construct(json_encode([
            'cause' => 'Доступ к запрашиваемому ресурсу запрещен',
            'type' => 'Forbidden'
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }

    public function getStatusCode() : int
    {
        return 403;
    }
}