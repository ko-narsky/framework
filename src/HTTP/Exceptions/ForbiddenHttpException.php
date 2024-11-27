<?php

namespace Konarsky\HTTP\Exceptions;

class ForbiddenHttpException extends HttpException
{
    public function __construct(string $message = 'Запрещено')
    {
        parent::__construct($message, 403);
    }
}