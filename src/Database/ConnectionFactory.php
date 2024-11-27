<?php

declare(strict_types=1);

namespace Konarsky\Database;

use Konarsky\Contracts\ConnectionFactoryInterface;
use Konarsky\Contracts\DataBaseConnectionInterface;

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
