<?php

namespace Konarsky\HTTP\Resource;

use Konarsky\Contract\DataBaseConnectionInterface;
use Konarsky\Contract\QueryBuilderInterface;
use Konarsky\Contract\ResourceDataFilterInterface;
use Konarsky\Database\QueryBuilderFactory;

class ResourceDataFilter implements ResourceDataFilterInterface
{
    private string $resourceName;
    private array $accessibleFields = [];
    private array $accessibleFilters = [];

    public function __construct(
        private readonly DataBaseConnectionInterface $connection,
        private readonly QueryBuilderFactory $queryBuilderFactory
    ) { }

    /**
     * @inheritDoc
     */
    public function setResourceName(string $name): static
    {
        $this->resourceName = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAccessibleFields(array $fieldNames): static
    {
        $this->accessibleFields = $fieldNames;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAccessibleFilters(array $filterNames): static
    {
        $this->accessibleFilters = $filterNames;

        return $this;
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
    public function filterOne(int|string $id, array $condition): array|null
    {
        return $this->connection->selectOne(
            $this->buildQuery($condition)
                ->where(['id' => $id])
        );

    }

    private function buildQuery(array $condition): QueryBuilderInterface
    {
        $fields = $this->resolveFields($condition['fields'] ?? []);
        $filters = $this->resolveFilters($condition['filter'] ?? []);

        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder->select($fields)
            ->from($this->resourceName);

        foreach ($filters as $field => $filter) {
            foreach ($filter as $operator => $value) {
                $this->applyFilter($queryBuilder, $field, $operator, $value);
            }
        }

        return $queryBuilder;
    }

    private function resolveFields(array $requestFields): array
    {
        if (empty($requestFields) === true) {
            return $this->accessibleFields;
        }

        return array_intersect($requestFields, $this->accessibleFields);
    }

    private function resolveFilters(array $requestFilters): array
    {
        $validFilters = [];

        foreach ($requestFilters as $field => $conditions) {
            if (in_array($field, $this->accessibleFilters, true) === true) {
                $validFilters[$field] = $conditions;
            }
        }

        return $validFilters;
    }

    private function applyFilter(QueryBuilderInterface $queryBuilder, string $field, string $operator, mixed $value): void
    {
        match ($operator) {
            '$eq' => $queryBuilder->where([$field => $value]),
            default => null
        };
    }
}
