<?php

declare(strict_types=1);

namespace Konarsky\Contract;

use Throwable;

interface ErrorMiddlewareInterface
{
    /**
     * @param Throwable $e
     *
     * @return void
     */
    public function __invoke(Throwable $e): void;
}
