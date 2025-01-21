<?php

namespace Konarsky\Database\File;

use Konarsky\Contract\QueryBuilderInterface;

interface FileQueryBuilderInterface extends QueryBuilderInterface
{
    /**
     * @return StatementParameters
     */
    public function getStatement(): StatementParameters;
}
