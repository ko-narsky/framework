<?php

namespace Konarsky\contracts;

interface DataBaseConnectionInterface
{
    /**
     * @param QueryBuilderInterface $query
     *
     * @return array
     */
    public function select(QueryBuilderInterface $query): array;

    /**
     * @param QueryBuilderInterface $query
     *
     * @return array|null
     */
    public function selectOne(QueryBuilderInterface $query): null|array;

    /**
     * @param QueryBuilderInterface $query
     *
     * @return array
     */
    public function selectColumn(QueryBuilderInterface $query): array;

    /**
     * @param QueryBuilderInterface $query
     *
     * @return mixed
     */
    public function selectScalar(QueryBuilderInterface $query): mixed;

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
