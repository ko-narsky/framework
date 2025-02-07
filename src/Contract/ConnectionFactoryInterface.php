<?php

namespace Konarsky\Contract;

interface ConnectionFactoryInterface
{
    /**
     * @param array $config
     *
     * @return DataBaseConnectionInterface
     */
    public function createConnection(array $config): DataBaseConnectionInterface;
}
