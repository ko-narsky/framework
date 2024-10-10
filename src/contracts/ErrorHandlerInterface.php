<?php

namespace Konarsky\contracts;

interface ErrorHandlerInterface
{
    /**
     * @param \Throwable $e объект ошибки
     * @return string
     */
    public function handle(\Throwable $e): string;
}