<?php

namespace Konarsky\HTTP\Responses;

class HtmlResponse
{
    public function __construct(
        public string $body,
    ) {
    }
}
