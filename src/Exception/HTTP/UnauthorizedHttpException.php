<?php

namespace Konarsky\Exception\HTTP;

class UnauthorizedHttpException extends HttpException
{
    public function __construct(string $message = 'Не авторизован')
    {
        parent::__construct($message, 401);
    }
}