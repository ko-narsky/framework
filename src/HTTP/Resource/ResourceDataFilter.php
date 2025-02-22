<?php

namespace Konarsky\HTTP\Resource;

use Konarsky\Contract\DataBaseConnectionInterface;
use Konarsky\Contract\QueryBuilderInterface;
use Konarsky\Contract\ResourceDataFilterInterface;
use Konarsky\Database\QueryBuilderFactory;
use Konarsky\HTTP\Enum\RelationshipTypeEnum;

class ResourceDataFilter implements ResourceDataFilterInterface
{
    private string $resourceName;
    private array $accessibleFields = [];
    private array $accessibleFilters = [];
    private array $relationships;

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
    public function setRelationships(array $relationships): static
    {
        $this->relationships = $relationships;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function filterAll(array $condition): array
    {
        $result = $this->connection->select($this->buildQuery($condition));

        foreach ($result as &$item) {
            $this->addExpands($item, $condition['expand'] ?? '');
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function filterOne(array $condition): array|null
    {
        $result =  $this->connection->selectOne($this->buildQuery($condition));

        $this->addExpands($result, $condition['expand'] ?? '');

        return $result;
    }

    private function buildQuery(array $condition): QueryBuilderInterface
    {
        $fields = $this->resolveFields($condition['fields'] ?? '');
        $filters = $this->resolveFilters($condition['filter'] ?? []);

        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder->select($fields[$this->resourceName])
            ->from($this->resourceName);

        foreach ($filters as $field => $filter) {
            foreach ($filter as $operator => $value) {
                $this->applyFilter($queryBuilder, $field, $operator, $value);
            }
        }

        return $queryBuilder;
    }

    private function resolveFields(string $requestFields): array
    {
        if (empty($requestFields) === true) {
            return [$this->resourceName => $this->accessibleFields];
        }

        $requestFields = explode(',', $requestFields);
        $fields = [];

        foreach ($requestFields as $field) {
            if (str_contains($field, '.') === false) {
                $fields[$this->resourceName][] = $field;

                continue;
            }

            [$relation, $column] = explode('.', $field, 2);
            $fields[$relation][] = $column;
        }

        return $fields;
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

    private function addExpands(array &$data, string $expands): void
    {
        if (empty($expands) === true) {
            return;
        }

        $expands = explode(',', $expands);

        foreach ($expands as $expand) {
            $relation = $this->relationships[$expand] ?? [];

            if ($relation === []) {
                return;
            }


            if($relation['type'] === RelationshipTypeEnum::ONE_TO_ONE->value) {
                $queryBuilder = $this->queryBuilderFactory->create();
                $queryBuilder->select('*')
                    ->from($relation['target_table'])
                    ->where([$relation['target_key'] => $data[$relation['resource_key']]]);

                $data['relationships'][$expand] = $this->connection->selectOne($queryBuilder);

                continue;
            }

            if($relation['type'] === RelationshipTypeEnum::ONE_TO_MANY->value) {

                $queryBuilder = $this->queryBuilderFactory->create();
                $queryBuilder->select(key($relation['target_key']))
                    ->from($relation['via_table'])
                    ->where([$relation['resource_key'] => $data['id']]);

                foreach ($this->connection->selectColumn($queryBuilder) as $column) {
                    $queryBuilder = $this->queryBuilderFactory->create();
                    $queryBuilder->select('*')
                        ->from($relation['target_table'])
                        ->where([current($relation['target_key']) => $column]);

                    $data['relationships'][$expand][] = $this->connection->selectOne($queryBuilder);
                }
            }
        }
    }
}
