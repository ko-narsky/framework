<?php

namespace Konarsky\contracts;

interface DataBaseConnectionInterface
{
    /**
     * @param QueryBuilder $query
     *
     * @return array
     */
    public function select(QueryBuilder $query): array;

    /**
     * @param QueryBuilder $query
     *
     * @return array|null
     */
    public function selectOne(QueryBuilder $query): null|array;

    /**
     * @param QueryBuilder $query
     *
     * @return array
     */
    public function selectColumn(QueryBuilder $query): array;

    /**
     * @param QueryBuilder $query
     *
     * @return mixed
     */
    public function selectScalar(QueryBuilder $query): mixed;

    /**
     * @param string $resource
     * @param array $data
     * @param array $condition
     *
     * @return int
     */
    public function update(string $resource, array $data, array $condition): int;

    /**
     * @param string $resource
     * @param array $data
     *
     * @return int
     */
    public function insert(string $resource, array $data): int;

    /**
     * @param string $resource
     * @param array $condition
     *
     * @return int
     */
    public function delete(string $resource, array $condition): int;

    /**
     * @return string
     */
    public function getLastInsertId(): string;
}
