<?php

declare(strict_types=1);

namespace Konarsky\Database\Mysql;

use Konarsky\Contract\QueryBuilderInterface;

class QueryBuilder implements QueryBuilderInterface
{
    private string $query = '';
    private array $params = [];
    private array $whereClause = [];

    public function select(string|array ...$fields): static
    {
        if (empty($fields) === true || (is_array($fields[0]) === true && empty($fields[0])) === true) {
            $this->query .= "SELECT * ";

            return $this;
        }

        $fields = is_array($fields[0]) ? $fields[0] : $fields;
        $this->query .= "SELECT " . implode(", ", $fields) . " ";

        return $this;
    }

    public function from(array $resources): static
    {
        $this->query .= "FROM " . implode(", ", $resources) . " ";

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
        $this->query .= strtoupper($type) . " JOIN $resource ON $on ";

        return $this;
    }

    public function orderBy(array $columns): static
    {
        $this->query .= "ORDER BY " . implode(", ", $columns) . " ";

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->query .= "LIMIT $limit ";

        return $this;
    }

    public function offset(int $offset): static
    {
        $this->query .= "OFFSET $offset ";

        return $this;
    }

    public function getStatement(): string
    {
        $this->buildWhereClause();

        return $this->query;
    }

    public function getParams(): array
    {
        return $this->params ?? [];
    }

    private function buildWhereClause(): void
    {
        if (empty($this->whereClause) === false) {
            $this->query .= "WHERE " . implode(" AND ", $this->whereClause) . " ";
        }
    }
}
