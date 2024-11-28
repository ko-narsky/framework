<?php

namespace Konarsky\Exception\HTTP;

class ForbiddenHttpException extends HttpException
{
    public function __construct(string $message = 'Запрещено')
    {
        parent::__construct($message, 403);
    }
}