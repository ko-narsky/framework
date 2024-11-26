<?php

namespace Konarsky\DB;

use Konarsky\contracts\QueryBuilderInterface;

class MariadbQueryBuilder implements QueryBuilderInterface
{
    private string $query = '';

    public function select(string|array ...$fields): static
    {
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
        $whereParts = [];
        foreach ($condition as $key => $value) {
            $param = ":where_$key";
            $whereParts[] = "$key = $param";
            $this->params[$param] = $value;
        }
        $this->query .= "WHERE " . implode(" AND ", $whereParts) . " ";
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
        $this->query .= "$column IN (" . implode(", ", $placeholders) . ") ";
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
        return $this->query;
    }
}