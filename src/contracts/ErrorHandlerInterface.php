<?php

namespace Konarsky\contracts;

use Throwable;

interface ErrorHandlerInterface
{
    /**
     * @param Throwable $e
     *
     * @return string
     */
    public function handle(Throwable $e): string;
}