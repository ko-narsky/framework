<?php

namespace Konarsky\Logger\Enum;

enum LogLevelNumber: int
{
    case DEBUG = 0;
    case INFO = 1;
    case WARNING = 2;
    case ERROR = 3;
    case CRITICAL = 4;

    public static function fromName(string $name): self
    {
        return constant("self::$name");
    }
}
