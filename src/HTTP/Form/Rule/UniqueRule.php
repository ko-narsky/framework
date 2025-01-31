<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\DataBaseConnectionInterface;
use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

class UniqueRule implements FormRequestRuleInterface
{
    public function __construct(
        private readonly DataBaseConnectionInterface $connection
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, array $options): void
    {
        if ($this->connection->isExist($options['resource'], $options['attribute'], $value) === true) {
            throw new ValidationException('Значение должно быть уникально');
        }
    }
}