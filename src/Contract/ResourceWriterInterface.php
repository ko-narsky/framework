<?php

namespace Konarsky\Contract;

use Konarsky\Exception\Base\NotFoundException;

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
     * @return void
     * @throws NotFoundException
     */
    public function update(string|int $id, array $values): void;

    /**
     * @param string|int $id
     * @param array $values
     * @return void
     * @throws NotFoundException
     */
    public function patch(string|int $id, array $values): void;

    /**
     * @param string|int $id
     * @return void
     * @throws NotFoundException
     */
    public function delete(string|int $id): void;
}
