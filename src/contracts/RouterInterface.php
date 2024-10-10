<?php

namespace Konarsky\contracts;

use Psr\Http\Message\RequestInterface;

interface RouterInterface
{
    public function dispatch(RequestInterface $request): mixed;
}