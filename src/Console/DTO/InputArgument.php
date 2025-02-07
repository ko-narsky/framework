<?php

namespace Konarsky\Console\DTO;

final class InputArgument
{
    public function __construct(
        public ?string $description = null,
        public ?bool   $required  = null,
        public ?string $defaultValue = null
    ) {
    }
}
