<?php

namespace Konarsky\HTTP\Responses;

class JsonResponse
{
    public function __construct(
        public mixed $body,
    ) {
    }
}
