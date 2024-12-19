<?php

namespace Konarsky\HTTP\Resource;

use Konarsky\Configuration\ConfigurationInterface;
use Konarsky\Contract\ConnectionFactoryInterface;
use Konarsky\Contract\DataBaseConnectionInterface;
use Konarsky\Contract\ResourceWriterInterface;

class ResourceWriter implements ResourceWriterInterface
{
    private string $resourceName;
    private readonly DataBaseConnectionInterface $connection;

    public function __construct(
        private readonly ConnectionFactoryInterface $connectionFactory,
        private readonly ConfigurationInterface $configuration,
    ) {
        $this->connection = $this->connectionFactory->createConnection($this->configuration->get('DB_CONFIGURATION'));
    }

    // TODO этого метода не было в интерфейсе
    public function setResourceName(string $resourceName): void
    {
        $this->resourceName = $resourceName;
    }

    /**
     * @inheritDoc
     */
    public function create(array $values): void
    {
        $this->connection->insert($this->resourceName, $values);
    }

    /**
     * @inheritDoc
     */
    public function update(int|string $id, array $values): void
    {
        $this->connection->update($this->resourceName, $values, ['id' => $id]);
    }

    /**
     * @inheritDoc
     */
    public function patch(int|string $id, array $values): void
    {
        $this->connection->update($this->resourceName, $values, ['id' => $id]);
    }

    /**
     * @inheritDoc
     */
    public function delete(int|string $id): void
    {
        $this->connection->delete($this->resourceName, ['id' => $id]);
    }
}
