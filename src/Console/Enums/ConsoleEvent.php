<?php

namespace Konarsky\Console\Enums;

enum ConsoleEvent
{
    case CONSOLE_INPUT_BEFORE_PARSE;
    case CONSOLE_INPUT_AFTER_PARSE;
}
