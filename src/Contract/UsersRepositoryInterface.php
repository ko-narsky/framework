<?php

namespace Konarsky\Contract;

interface UsersRepositoryInterface
{
    public function isExistBy(string $column, string|int $value): bool;
}