<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class RequiredRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, mixed $options): void
    {
        if ($value === null || $value === '' || (is_array($value) === true && empty($value) === true)) {
            throw new ValidationException('Обязательное значение');
        }
    }
}
