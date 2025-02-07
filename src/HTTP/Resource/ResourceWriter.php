<?php

namespace Konarsky\HTTP\Resource;

use Konarsky\Contract\DataBaseConnectionInterface;
use Konarsky\Contract\ResourceWriterInterface;

class ResourceWriter implements ResourceWriterInterface
{
    private string $resourceName;

    public function __construct(
        private readonly DataBaseConnectionInterface $connection
    ) { }

    public function setResourceName(string $name): static
    {
        $this->resourceName = $name;

        return $this;
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
