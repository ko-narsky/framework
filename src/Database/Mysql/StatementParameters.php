<?php

namespace Konarsky\Database\Mysql;

final readonly class StatementParameters
{
    public function __construct(
        public string $sql,
        public array $bindings
    ) {
    }
}
