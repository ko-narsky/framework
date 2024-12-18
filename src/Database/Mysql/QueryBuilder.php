<?php

declare(strict_types=1);

namespace Konarsky\Database\Mysql;

use Konarsky\Contract\QueryBuilderInterface;

class QueryBuilder implements QueryBuilderInterface
{
    private array $selectFields = [];
    private string $resource;
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

    public function from(string|array $resource): static
    {
        if (is_string($resource) === true) {
            $this->resource = $resource;
        }

        if (is_array($resource) === true) {
            $this->resource = key($resource) . ' AS ' . current($resource);
        }

        if (is_array($resource) === true && array_is_list($resource) === true) {
            $this->resource = $resource[0];
        }

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

        $this->whereClause[] = "$column IN (" . implode(', ', $placeholders) . ')';

        return $this;
    }

    public function join(string $type, string|array $resource, string $on): static
    {
        if (is_array($resource) === true && array_is_list($resource) === false) {
            $resource = key($resource) . ' AS ' . current($resource);
        }

        if (is_array($resource) === true && array_is_list($resource) === true) {
            $resource = $resource[0];
        }

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
            $query[] = 'SELECT ' . implode(', ', $this->selectFields);
        }

        if (isset($this->resource) === true) {
            $query[] = 'FROM ' . $this->resource;
        }

        if (empty($this->joinClause) === false) {
            $query[] = implode(" ", $this->joinClause);
        }

        if (empty($this->whereClause) === false) {
            $query[] = 'WHERE ' . implode(' AND ', $this->whereClause);
        }

        if (empty($this->groupByClause) === false) {
            $query[] = 'GROUP BY ' . implode(', ', $this->groupByClause);
        }

        if (empty($this->orderByClause) === false) {
            $query[] = 'ORDER BY ' . implode(', ', $this->orderByClause);
        }

        if ($this->limit !== null) {
            $query[] = "LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $query[] = "OFFSET {$this->offset}";
        }

        return implode(' ', $query);
    }

    public function getParams(): array
    {
        return $this->params;
    }
}

//public function getStatement(): object
//{
//    $query = [];
//
//    if (!empty($this->selectFields)) {
//        $query[] = "SELECT " . implode(", ", $this->selectFields);
//    }
//
//    if (isset($this->resource)) {
//        $query[] = "FROM " . $this->resource;
//    }
//
//    if (!empty($this->joinClause)) {
//        $query[] = implode(" ", $this->joinClause);
//    }
//
//    if (!empty($this->whereClause)) {
//        $query[] = "WHERE " . implode(" AND ", $this->whereClause);
//    }
//
//    if (!empty($this->groupByClause)) {
//        $query[] = "GROUP BY " . implode(", ", $this->groupByClause);
//    }
//
//    if (!empty($this->orderByClause)) {
//        $query[] = "ORDER BY " . implode(", ", $this->orderByClause);
//    }
//
//    if ($this->limit !== null) {
//        $query[] = "LIMIT {$this->limit}";
//    }
//
//    if ($this->offset !== null) {
//        $query[] = "OFFSET {$this->offset}";
//    }
//
//    return (object)[
//        'sql' => implode(" ", $query),
//        'params' => $this->params,
//    ];
//}
//
//$queryBuilder = new QueryBuilder();
//
//$sql = $queryBuilder
//    ->select('*')
//    ->from(['months' => 'm'])
//    ->where(['name' => 'октябрь'])
//    ->join('left', ['prices' => 'p'], 'p.month_id = m.id');
//
//$statement = $this->pdo->prepare($sql->getStatement()->sql);
//$statement->execute($sql->getStatement()->params);
//$res = $statement->fetchAll(PDO::FETCH_ASSOC);




//public function getStatement(PDO $pdo): PDOStatement
//{
//    $query = [];
//
//    if (!empty($this->selectFields)) {
//        $query[] = "SELECT " . implode(", ", $this->selectFields);
//    }
//
//    if (isset($this->resource)) {
//        $query[] = "FROM " . $this->resource;
//    }
//
//    if (!empty($this->joinClause)) {
//        $query[] = implode(" ", $this->joinClause);
//    }
//
//    if (!empty($this->whereClause)) {
//        $query[] = "WHERE " . implode(" AND ", $this->whereClause);
//    }
//
//    if (!empty($this->groupByClause)) {
//        $query[] = "GROUP BY " . implode(", ", $this->groupByClause);
//    }
//
//    if (!empty($this->orderByClause)) {
//        $query[] = "ORDER BY " . implode(", ", $this->orderByClause);
//    }
//
//    if ($this->limit !== null) {
//        $query[] = "LIMIT {$this->limit}";
//    }
//
//    if ($this->offset !== null) {
//        $query[] = "OFFSET {$this->offset}";
//    }
//
//    $statement = $pdo->prepare(implode(" ", $query));
//    foreach ($this->params as $param => $value) {
//        $statement->bindValue($param, $value);
//    }
//
//    return $statement;
//}
//
//$queryBuilder = new QueryBuilder();
//
//$statement = $queryBuilder
//    ->select('*')
//    ->from(['months' => 'm'])
//    ->where(['name' => 'октябрь'])
//    ->join('left', ['prices' => 'p'], 'p.month_id = m.id')
//    ->getStatement($this->pdo); // Передаём PDO сюда
//
//$statement->execute();
//$res = $statement->fetchAll(PDO::FETCH_ASSOC);
//
//print_r($res);
