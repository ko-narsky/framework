<?php

namespace Konarsky\http\response;

class HtmlResponse
{
    public function __construct(
        public string $body,
    ) {
    }
}
