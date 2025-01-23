<?php

namespace Konarsky\Database\File;

use InvalidArgumentException;
use Konarsky\Contract\DataBaseConnectionInterface;
use Konarsky\Contract\QueryBuilderInterface;

class Connection implements DataBaseConnectionInterface
{

    private string $directory;
    private int $lastInsertId;

    public function __construct(string $directory)
    {
        if (is_dir($directory) === false) {
            throw new InvalidArgumentException("Директория $directory не существует");
        }

        $this->directory = rtrim($directory, DIRECTORY_SEPARATOR);
    }

    public function select(QueryBuilderInterface $query): array
    {
        $statement = $query->getStatement();

        $data = $this->readFile($statement->resource);

        $data = $this->applyWhere($data, $statement->whereClause);

        $data = $this->applyOrderBy($data, $statement->orderByClause);

        $data = $this->applySelectFields($data, $statement->selectFields);

        return $this->applyLimitOffset($data, $statement->limit, $statement->offset);
    }

    public function selectOne(QueryBuilderInterface $query): null|array
    {
        $result = $this->select($query);

        return $result[0] ?? null;
    }

    public function selectColumn(QueryBuilderInterface $query): array
    {
        $result = $this->select($query);
        $field = $query->getStatement()->selectFields[0] ?? null;

        return array_column($result, $field);
    }

    public function selectScalar(QueryBuilderInterface $query): mixed
    {
        $result = $this->selectColumn($query);

        return $result[0] ?? null;
    }

    public function update(string $resource, array $data, array $condition): int
    {
        $fileData = $this->readFile($resource);
        $updatedCount = 0;

        foreach ($fileData as &$row) {
            if ($this->matchesCondition($row, $condition) === false) {
                continue;
            }

            foreach ($data as $key => $value) {
                $row[$key] = $value;
            }

            $updatedCount++;
        }


        $this->writeFile($resource, $fileData);

        return $updatedCount;
    }

    public function insert(string $resource, array $data): int
    {
        $fileData = $this->readFile($resource);

        $maxId = 0;

        foreach ($fileData as $row) {
            if (isset($row['id']) && $row['id'] > $maxId) {
                $maxId = $row['id'];
            }
        }

        $this->lastInsertId = $maxId + 1;

        $data['id'] =  $this->lastInsertId;

        $fileData[] = $data;

        $this->writeFile($resource, $fileData);

        return  $this->lastInsertId;
    }

    public function delete(string $resource, array $condition): int
    {
        $fileData = $this->readFile($resource);
        $initialCount = count($fileData);

        $fileData = array_filter($fileData, static fn($row) => $this->matchesCondition($row, $condition) === false);

        $this->writeFile($resource, array_values($fileData));

        return $initialCount - count($fileData);
    }

    public function getLastInsertId(): string
    {
        return isset($this->lastInsertId) === true ? $this->lastInsertId : '';
    }

    private function readFile(string $resource): array
    {
        $path = $this->getPath($resource);

        if (file_exists($path) === false) {
            return [];
        }

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    private function writeFile(string $resource, array $data): void
    {
        file_put_contents($this->getPath($resource), json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function getPath(string $resource): string
    {
        return "{$this->directory}/{$resource}.json";
    }

    private function matchesCondition(array $row, array $condition): bool
    {
        foreach ($condition as $key => $value) {
            if (is_array($value) === true) {
                return in_array($row[$key], $value);
            }

            if (array_key_exists($key, $row) === false || $row[$key] !== $value) {
                return false;
            }
        }

        return true;
    }

    private function applyWhere(array $data, array $whereClause): array
    {
        foreach ($whereClause as $condition) {
            $data = array_filter($data, static fn ($row) => $this->matchesCondition($row, $condition));
        }

        return $data;
    }


    private function applyOrderBy(array $data, array $orderByClause): array
    {
        foreach ($orderByClause as $clause) {
                usort($data, static fn ($a, $b) => $a[$clause] <=> $b[$clause]);
        }

        return $data;
    }

    private function applyLimitOffset(array $data, ?int $limit, ?int $offset): array
    {
        return array_slice($data, $offset ?? 0, $limit);
    }

    private function applySelectFields(array $data, ?array $selectFields): array
    {
        if (empty($selectFields) === true) {
            return $data;
        }

        return array_map(
            static fn ($item) => array_intersect_key($item, array_flip($selectFields)),
            $data
        );
    }
}