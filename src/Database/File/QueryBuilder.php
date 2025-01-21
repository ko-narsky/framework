<?php

declare(strict_types=1);

namespace Konarsky\Database\File;

class QueryBuilder implements FileQueryBuilderInterface
{
    private array $selectFields = [];
    private string $resource;
    private array $whereClause = [];
    private array $orderByClause = [];
    private ?int $limit = null;
    private ?int $offset = null;

    /**
     * @inheritDoc
     */
    public function select(string|array ...$fields): static
    {
        $this->selectFields = is_array($fields[0]) ? $fields[0] : $fields;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function from(array|string $resource): static
    {
        $this->resource = is_string($resource) ? $resource : key($resource);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function where(array $condition): static
    {
        $this->whereClause[] = $condition;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function whereIn(string $column, array $values): static
    {
        $this->whereClause[] = [$column => $values];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function join(string $type, array|string $resource, string $on): static
    {
        // Not implemented

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function orderBy(array $columns): static
    {
        $this->orderByClause = $columns;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function offset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    public function getStatement(): StatementParameters
    {
        return new StatementParameters(
            resource: $this->resource,
            selectFields: $this->selectFields,
            whereClause: $this->whereClause,
            orderByClause: $this->orderByClause,
            limit: $this->limit,
            offset: $this->offset
        );
    }
}
