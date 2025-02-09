<?php

declare(strict_types=1);

namespace Konarsky\Database\Mysql;

use Konarsky\Contract\DataBaseConnectionInterface;
use Konarsky\Contract\QueryBuilderInterface;
use Konarsky\Exception\Base\NotFoundException;
use PDO;

class Connection implements DataBaseConnectionInterface
{
    private PDO $connection;

    public function __construct(array $config)
    {
        $dsn = sprintf("mysql:host=%s;dbname=%s;charset=%s", $config['host'], $config['dbname'], $config['charset']);
        $this->connection = new PDO($dsn, $config['username'], $config['password']);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function select(QueryBuilderInterface $query): array
    {
        $statement = $this->connection->prepare($query->getStatement()->sql);
        $statement->execute($query->getStatement()->bindings);

        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($data === false) {
            return [];
        }

        return $data;
    }

    public function selectOne(QueryBuilderInterface $query): null|array
    {
        $statement = $this->connection->prepare($query->getStatement()->sql);
        $statement->execute($query->getStatement()->bindings);

        $data = $statement->fetch(PDO::FETCH_ASSOC) ?: null;

        if ($data === false) {
            return null;
        }

        return $data;
    }

    public function selectColumn(QueryBuilderInterface $query): array
    {
        $statement = $this->connection->prepare($query->getStatement()->sql);
        $statement->execute($query->getStatement()->bindings);

        $data = $statement->fetchAll(PDO::FETCH_COLUMN);

        if ($data === false) {
            return [];
        }

        return $data;
    }

    public function selectScalar(QueryBuilderInterface $query): mixed
    {
        $statement = $this->connection->prepare($query->getStatement()->sql);
        $statement->execute($query->getStatement()->bindings);

        $data = $statement->fetchColumn();

        if ($data === false) {
            return null;
        }

        return $data;
    }

    public function update(string $resource, array $data, array $condition): int
    {
        $setPart = implode(', ', array_map(fn($col) => "$col = :$col", array_keys($data)));

        $queryBuilder = new QueryBuilder();
        $wherePart = $queryBuilder->where($condition)->getStatement()->sql;

        $sql = "UPDATE $resource SET $setPart $wherePart";

        $statement = $this->connection->prepare($sql);

        foreach ($data as $key => $value) {
            if (is_string($value) === true) {
                $value = strtotime($value) === false ? $value :date('Y-m-d H:i:s',  strtotime($value));
            }

            $statement->bindValue(":$key", $value);
        }

        foreach ($condition as $key => $value) {
            $statement->bindValue(":where_$key", $value);
        }

        $statement->execute();

        return $statement->rowCount();
    }

    public function insert(string $resource, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($col) => ":$col", array_keys($data)));
        $sql = "INSERT INTO $resource ($columns) VALUES ($placeholders)";

        $statement = $this->connection->prepare($sql);

        foreach ($data as $key => $value) {
            if (is_string($value) === true) {
                $value = strtotime($value) === false ? $value :date('Y-m-d H:i:s',  strtotime($value));
            }

            $statement->bindValue(":$key", $value);
        }

        $statement->execute();

        return (int)$this->connection->lastInsertId();
    }

    public function delete(string $resource, array $condition): int
    {
        $queryBuilder = new QueryBuilder();
        $wherePart = $queryBuilder->where($condition)->getStatement()->sql;

        $sql = "DELETE FROM $resource $wherePart";

        $statement = $this->connection->prepare($sql);

        foreach ($condition as $key => $value) {
            $statement->bindValue(":where_$key", $value);
        }

        $statement->execute($queryBuilder->getStatement()->bindings);

        return $statement->rowCount();
    }

    public function getLastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    public function isExist(string $resource, string $column, mixed $value): bool
    {
        $sql = "SELECT EXISTS(SELECT 1 FROM $resource WHERE $column = :value)";

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':value', $value);
        $statement->execute();

        return (bool) $statement->fetchColumn();
    }
}
