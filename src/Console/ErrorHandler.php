<?php

namespace Konarsky\Console;

use Konarsky\Console\Enums\ConsoleColors;
use Konarsky\Contracts\ErrorHandlerInterface;

final readonly class ErrorHandler implements ErrorHandlerInterface
{
    public function handle(\Throwable $e): string
    {
        $outputString = "\033[41m";
        $outputString .= str_pad("[" . get_class($e) . "] {$e->getMessage()}", 150, ' ');
        $outputString .= "\n";
        $outputString .= str_pad("{$e->getFile()} on line {$e->getLine()}", 150, ' ');
        $outputString .= ConsoleColors::FG_COLOR_RESET->value . "\n\n";
        $outputString .= $e->getTraceAsString();
        $outputString .= "\n";

        return $outputString;
    }
}
