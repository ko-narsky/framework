<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class NotInRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, mixed $options): void
    {
        if (in_array($value, $options, true) === true) {
            throw new ValidationException("Значение '$value' недопустимо. Недопустимые значения: " . implode(', ', $options));
        }
    }
}
