<?php

namespace Konarsky\HTTP\Exceptions;

class NotFoundHttpException extends HttpException
{
    public function __construct(string $message = 'Не найдено')
    {
        parent::__construct($message, 404);
    }
}
