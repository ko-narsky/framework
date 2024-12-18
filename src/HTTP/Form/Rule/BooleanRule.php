<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class BooleanRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, mixed $options): void
    {
        if (in_array($value, [true, false, 1, 0, '1', '0'], true) === false) {
            throw new ValidationException('Значение должно быть булевым');
        }
    }
}
