<?php

namespace Konarsky\HTTP\Response;

class JsonResponse
{
    public function __construct(
        public mixed $body,
    ) {
    }
}
