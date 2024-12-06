<?php

declare(strict_types=1);

namespace Konarsky\Database\Mysql;

use Konarsky\Contract\QueryBuilderInterface;

class QueryBuilder implements QueryBuilderInterface
{
    private array $selectFields = [];
    private array $resources = [];
    private array $whereClause = [];
    private array $joinClause = [];
    private array $orderByClause = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $groupByClause = [];
    private array $params = [];

    public function select(string|array ...$fields): static
    {
        $this->selectFields = is_array($fields[0]) ? $fields[0] : $fields;

        return $this;
    }

    public function from(array $resources): static
    {
        $this->resources = $resources;

        return $this;
    }

    public function where(array $condition): static
    {
        foreach ($condition as $key => $value) {
            $safeKey = str_replace('.', '_', $key);
            $param = ":where_$safeKey";
            $this->whereClause[] = "$key = $param";
            $this->params[$param] = $value;
        }

        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        $placeholders = [];

        foreach ($values as $index => $value) {
            $placeholder = ":where_in_{$column}_$index";
            $placeholders[] = $placeholder;
            $this->params[$placeholder] = $value;
        }

        $this->whereClause[] = "$column IN (" . implode(", ", $placeholders) . ")";

        return $this;
    }

    public function join(string $type, string $resource, string $on): static
    {
        $this->joinClause[] = strtoupper($type) . " JOIN $resource ON $on";

        return $this;
    }

    public function orderBy(array $columns): static
    {
        $this->orderByClause = $columns;

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    public function groupBy(array $columns): static
    {
        $this->groupByClause = $columns;

        return $this;
    }

    public function getStatement(): string
    {
        $query = [];

        if (empty($this->selectFields) === false) {
            $query[] = "SELECT " . implode(", ", $this->selectFields);
        }

        if (empty($this->resources) === false) {
            $query[] = "FROM " . implode(", ", $this->resources);
        }

        if (empty($this->joinClause) === false) {
            $query[] = implode(" ", $this->joinClause);
        }

        if (empty($this->whereClause) === false) {
            $query[] = "WHERE " . implode(" AND ", $this->whereClause);
        }

        if (empty($this->groupByClause) === false) {
            $query[] = "GROUP BY " . implode(", ", $this->groupByClause);
        }

        if (empty($this->orderByClause) === false) {
            $query[] = "ORDER BY " . implode(", ", $this->orderByClause);
        }

        if ($this->limit !== null) {
            $query[] = "LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $query[] = "OFFSET {$this->offset}";
        }

        return implode(" ", $query);
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
