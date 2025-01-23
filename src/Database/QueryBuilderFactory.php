<?php

declare(strict_types=1);

namespace Konarsky\Database;

use Konarsky\Contract\QueryBuilderInterface;

class QueryBuilderFactory
{
    public function __construct(
        private readonly array $config,
    ) { }

    public function create(): QueryBuilderInterface
    {
        return match ($this->config['driver']) {
            'mysql' => new Mysql\QueryBuilder(),
            'file' => new File\QueryBuilder(),
        };
    }
}
