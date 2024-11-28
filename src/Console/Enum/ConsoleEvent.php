<?php

namespace Konarsky\Console\Enum;

enum ConsoleEvent
{
    case CONSOLE_INPUT_BEFORE_PARSE;
    case CONSOLE_INPUT_AFTER_PARSE;
}
