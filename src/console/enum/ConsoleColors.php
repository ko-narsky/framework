<?php

namespace Konarsky\console\enum;

enum ConsoleColors: string
{
    case FG_RED = "31";
    case FG_GREEN = "32";
    case FG_YELLOW = "33";
    case FG_BLUE = "34";
    case FG_CYAN = "36";
    case FG_COLOR_RESET = "0";
}
