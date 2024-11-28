<?php

namespace Konarsky\Contract;

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