<?php

namespace Konarsky\Contracts;

interface QueryBuilderInterface
{
    /**
     * @param array|string ...$fields
     *
     * @return $this
     */
    public function select(array|string ...$fields): static;

    /**
     * @param array $resources
     *
     * @return $this
     */
    public function from(array $resources): static;

    /**
     * @param array $condition
     *
     * @return $this
     */
    public function where(array $condition): static;

    /**
     * @param string $column
     * @param array $values
     *
     * @return $this
     */
    public function whereIn(string $column, array $values): static;

    /**
     * @param string $type
     * @param string $resource
     * @param string $on
     *
     * @return $this
     */
    public function join(string $type, string $resource, string $on): static;

    /**
     * @param array $columns
     *
     * @return $this
     */
    public function orderBy(array $columns): static;

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function limit(int $limit): static;

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function offset(int $offset): static;

    /**
     * @return mixed
     */
    public function getStatement(): mixed;
}
