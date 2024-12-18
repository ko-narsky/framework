<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class NumberRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, mixed $options): void
    {
        if (is_numeric($value) === false) {
            throw new ValidationException('Значение должно быть числом');
        }
    }
}
