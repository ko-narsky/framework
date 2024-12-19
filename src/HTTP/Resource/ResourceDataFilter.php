<?php

namespace Konarsky\HTTP\Resource;

use Konarsky\Configuration\ConfigurationInterface;
use Konarsky\Contract\ConnectionFactoryInterface;
use Konarsky\Contract\DataBaseConnectionInterface;
use Konarsky\Contract\QueryBuilderInterface;
use Konarsky\Contract\ResourceDataFilterInterface;
use Konarsky\Database\Mysql\QueryBuilder;

class ResourceDataFilter implements ResourceDataFilterInterface
{
    private string $resourceName;
    private array $accessibleFields = [];
    private array $accessibleFilters = [];
    private readonly DataBaseConnectionInterface $connection;

    public function __construct(
        private readonly ConnectionFactoryInterface $connectionFactory,
        private readonly ConfigurationInterface $configuration,
    ) {
        $this->connection = $this->connectionFactory->createConnection($this->configuration->get('DB_CONFIGURATION'));
    }
    /**
     * @inheritDoc
     */
    public function setResourceName(string $name): void
    {
        $this->resourceName = $name;
    }

    /**
     * @inheritDoc
     */
    public function setAccessibleFields(array $fieldNames): void
    {
        $this->accessibleFields = $fieldNames;
    }

    /**
     * @inheritDoc
     */
    public function setAccessibleFilters(array $filterNames): void
    {
        $this->accessibleFilters = $filterNames;
    }

    /**
     * @inheritDoc
     */
    public function filterAll(array $condition): array
    {
        return $this->connection->select($this->buildQuery($condition));
    }

    /**
     * @inheritDoc
     */
    public function filterOne(array $condition): array
    {
        return $this->connection->selectOne($this->buildQuery($condition));

    }

    private function buildQuery(array $condition): QueryBuilderInterface
    {
        $fields = $this->resolveFields($condition['fields'] ?? []);
        $filters = $this->resolveFilters($condition['filter'] ?? []);

        $queryBuilder = new QueryBuilder();
        $queryBuilder->select($fields)
            ->from($this->resourceName)
            ->where($filters);

        return $queryBuilder;
    }

    private function resolveFields(array $requestedFields): array
    {
        if (empty($requestedFields) === true) {
            return $this->accessibleFields;
        }

        return array_intersect($requestedFields, $this->accessibleFields);
    }

    private function resolveFilters(array $requestedFilters): array
    {
        $validFilters = [];

        foreach ($requestedFilters as $field => $conditions) {
            if (in_array($field, $this->accessibleFilters, true)) {
                $validFilters[$field] = $conditions;
            }
        }

        return $validFilters;
    }
}
