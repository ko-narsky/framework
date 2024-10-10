<?php

namespace Konarsky\logger;

final class LogStorageDto
{
    public string $index;

    public string|array|null $context = [];

    public int $level;

    public string $level_name;

    public string $action_type;

    public string $datetime;

    public string $timestamp;

    public string $x_debug_tag;

    public string $message;

    public string|null $category;

    public mixed $exception = null;

    public string|null $extras = null;
}
