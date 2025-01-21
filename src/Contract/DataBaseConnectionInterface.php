<?php

namespace Konarsky\Contract;

use Konarsky\Database\Mysql\MysqlQueryBuilderInterface;

interface DataBaseConnectionInterface
{
    /**
     * @param MysqlQueryBuilderInterface $query
     *
     * @return array
     */
    public function select(MysqlQueryBuilderInterface $query): array;

    /**
     * @param MysqlQueryBuilderInterface $query
     *
     * @return array|null
     */
    public function selectOne(MysqlQueryBuilderInterface $query): null|array;

    /**
     * @param MysqlQueryBuilderInterface $query
     *
     * @return array
     */
    public function selectColumn(MysqlQueryBuilderInterface $query): array;

    /**
     * @param MysqlQueryBuilderInterface $query
     *
     * @return mixed
     */
    public function selectScalar(MysqlQueryBuilderInterface $query): mixed;

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
