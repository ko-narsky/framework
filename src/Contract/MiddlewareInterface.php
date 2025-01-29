<?php

namespace Konarsky\Contract;

use Psr\Http\Message\ServerRequestInterface;

interface MiddlewareInterface
{
    public function __invoke(ServerRequestInterface $request);
}
