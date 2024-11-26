<?php

namespace Konarsky\contracts;

interface ConnectionFactoryInterface
{
    /**
     * @param array $config
     *
     * @return DataBaseConnectionInterface
     */
    public function createConnection(array $config): DataBaseConnectionInterface;
}
