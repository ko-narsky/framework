<?php

declare(strict_types=1);

namespace Konarsky\DB;

use Konarsky\contracts\ConnectionFactoryInterface;
use Konarsky\contracts\DataBaseConnectionInterface;

class ConnectionFactory implements ConnectionFactoryInterface
{

    /**
     * @inheritDoc
     */
    public function createConnection(array $config): DataBaseConnectionInterface
    {
        return match ($config['driver']) {
            'mysql' => new Mysql\Connection($config),
            'file' => new File\Connection($config),
        };
    }
}
