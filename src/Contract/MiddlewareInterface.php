<?php

namespace Konarsky\Contract;

use Psr\Http\Message\RequestInterface;

interface MiddlewareInterface
{
    public function __invoke(RequestInterface $request);
}
