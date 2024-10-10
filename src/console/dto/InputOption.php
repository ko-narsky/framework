<?php

namespace Konarsky\console\dto;

final class InputOption
{
    public function __construct(public ?string $description = null)
    {
    }
}
