<?php

namespace Konarsky\Contracts;

use Psr\Http\Message\RequestInterface;

interface RouterInterface
{
    public function dispatch(RequestInterface $request): mixed;
}