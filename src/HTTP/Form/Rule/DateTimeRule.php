<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class DateTimeRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, mixed $options): void
    {
        if (is_string($value) === false || strtotime($value) === false) {
            throw new ValidationException('Значение должно соответствовать типу Дата и время');
        }
    }
}
