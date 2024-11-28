<?php

namespace Konarsky\HTTP\Response;

class HtmlResponse
{
    public function __construct(
        public string $body,
    ) {
    }
}
