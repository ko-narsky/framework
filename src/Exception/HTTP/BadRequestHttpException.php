<?php

namespace Konarsky\Exception\HTTP;

class BadRequestHttpException extends HttpException
{
    public function __construct(string $message = 'Неправильный запрос')
    {
        parent::__construct($message, 400);
    }
}
