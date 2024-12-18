<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class InRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, mixed $options): void
    {
        if (in_array($value, $options, true) === false) {
            throw new ValidationException('Недопустимое значение. Допустимые значения: ' . implode(', ', $options));
        }
    }
}