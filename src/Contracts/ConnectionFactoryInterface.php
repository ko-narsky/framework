<?php

namespace Konarsky\Contracts;

interface ConnectionFactoryInterface
{
    /**
     * @param array $config
     *
     * @return DataBaseConnectionInterface
     */
    public function createConnection(array $config): DataBaseConnectionInterface;
}
