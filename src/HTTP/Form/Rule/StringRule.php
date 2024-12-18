<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class StringRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, mixed $options): void
    {
        if (is_string($value) === false || trim($value) === '') {
            throw new ValidationException('Значение должно быть строкой');
        }
    }
}
