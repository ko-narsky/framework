<?php

namespace Konarsky\http\response;

class JsonResponse
{
    public function __construct(
        public mixed $body,
    ) {
    }
}
