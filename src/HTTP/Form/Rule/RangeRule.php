<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class RangeRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, array $options): void
    {
        if (key_exists('min', $options) === true && $value < $options['min']) {
            throw new ValidationException('Минимальное значение ' . $options['min']);
        }

        if (key_exists('max', $options) === true && $value > $options['max']) {
            throw new ValidationException('Максимальное значение ' . $options['max']);
        }
    }
}
