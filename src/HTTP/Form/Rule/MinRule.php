<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class MinRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, array $options): void
    {
        // TODO нужно ли добавить условие is_numeric($value)?
        if ($value < $options) {
            throw new ValidationException('Значение должно быть больше ' . $options);
        }
    }
}
