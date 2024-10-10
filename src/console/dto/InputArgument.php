<?php

namespace Konarsky\console\dto;

final class InputArgument
{
    public function __construct(
        public ?string $description = null,
        public ?bool   $required  = null,
        public ?string $defaultValue = null
    ) {
    }
}
