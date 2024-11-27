<?php

declare(strict_types=1);

namespace Konarsky\Database\Mysql;

use Konarsky\Contracts\DataBaseConnectionInterface;
use Konarsky\Contracts\QueryBuilderInterface;
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
        $stmt = $this->connection->prepare($query->getStatement());
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectOne(QueryBuilderInterface $query): null|array
    {
        $stmt = $this->connection->prepare($query->getStatement());
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function selectColumn(QueryBuilderInterface $query): array
    {
        $stmt = $this->connection->prepare($query->getStatement());
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function selectScalar(QueryBuilderInterface $query): mixed
    {
        $stmt = $this->connection->prepare($query->getStatement());
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function update(string $resource, array $data, array $condition): int
    {
        $setPart = implode(", ", array_map(fn($col) => "$col = :$col", array_keys($data)));
        $wherePart = implode(" AND ", array_map(fn($col) => "$col = :cond_$col", array_keys($condition)));
        $sql = "UPDATE $resource SET $setPart WHERE $wherePart";

        $stmt = $this->connection->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        foreach ($condition as $key => $value) {
            $stmt->bindValue(":cond_$key", $value);
        }

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function insert(string $resource, array $data): int
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_map(fn($col) => ":$col", array_keys($data)));
        $sql = "INSERT INTO $resource ($columns) VALUES ($placeholders)";

        $stmt = $this->connection->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();

        return (int)$this->connection->lastInsertId();
    }

    public function delete(string $resource, array $condition): int
    {
        $wherePart = implode(" AND ", array_map(fn($col) => "$col = :$col", array_keys($condition)));
        $sql = "DELETE FROM $resource WHERE $wherePart";

        $stmt = $this->connection->prepare($sql);

        foreach ($condition as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function getLastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }
}
