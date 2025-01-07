<?php

namespace Konarsky\Contract;

interface ResourceWriterInterface
{
    function setResourceName(string $name): static;
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
