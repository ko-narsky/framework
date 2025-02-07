<?php

namespace Konarsky\Database\Mysql;

use Konarsky\Contract\QueryBuilderInterface;

interface MysqlQueryBuilderInterface extends QueryBuilderInterface
{
    /**
     * @return StatementParameters
     */
    public function getStatement(): StatementParameters;
}