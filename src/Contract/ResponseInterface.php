<?php

namespace Konarsky\Contract;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
interface ResponseInterface extends PsrResponseInterface
{
    /**
     * @return void
     */
    public function send(): void;
}
