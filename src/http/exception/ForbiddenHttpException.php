<?php

namespace Konarsky\http\exception;

class ForbiddenHttpException extends HttpException
{
    public function __construct(string $message = 'Запрещено')
    {
        parent::__construct($message, 403);
    }
}