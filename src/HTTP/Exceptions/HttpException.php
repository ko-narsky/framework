<?php

namespace Konarsky\HTTP\Exceptions;

use Exception;

class HttpException extends Exception
{

    public function __construct(string $message, private readonly int $statusCode) {
        parent::__construct($message);
    }

    public function getStatusCode() : int
    {
        return $this->statusCode;
    }
}