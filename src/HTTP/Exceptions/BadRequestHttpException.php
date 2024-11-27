<?php

namespace Konarsky\HTTP\Exceptions;

class BadRequestHttpException extends HttpException
{
    public function __construct(string $message = 'Неправильный запрос')
    {
        parent::__construct($message, 400);
    }
}
