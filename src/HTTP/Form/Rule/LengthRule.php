<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class LengthRule implements FormRequestRuleInterface
{
    /**
     * @inheritDoc
     */
    public function validate(mixed $value, array $options): void
    {
        $length = mb_strlen($value);

        if (key_exists('min', $options) === true && $length < $options['min']) {
            throw new ValidationException('Минимальное количество символов в строке: ' . $options['min']);
        }

        if (key_exists('max', $options) === true && $length > $options['max']) {
            throw new ValidationException('Максимальное количество символов в строке: ' . $options['max']);
        }
    }
}
