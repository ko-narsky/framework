<?php

namespace Konarsky\Contract;

use Psr\Http\Message\RequestInterface;

interface RouterInterface
{
    public function dispatch(RequestInterface $request): mixed;
}