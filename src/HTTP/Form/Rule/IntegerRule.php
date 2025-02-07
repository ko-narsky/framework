<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class IntegerRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, array $options): void
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new ValidationException('Значение должно быть целым числом');
        }
    }
}
