<?php

namespace Konarsky\contracts;

interface ResourceWriterInterface
{
    /**
     * @param array $values
     *
     * @return void
     */
    public function create(array $values): void;

    /**
     * @param string|int $id
     * @param array $values
     *
     * @return void
     */
    public function update(string|int $id, array $values): void;

    /**
     * @param string|int $id
     * @param array $values
     *
     * @return void
     */
    public function patch(string|int $id, array $values): void;

    /**
     * @param string|int $id
     *
     * @return void
     */
    public function delete(string|int $id): void;
}
