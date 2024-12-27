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
            throw new ValidationException('Минимальная длина строки ' . $options['min'] . ' ' . $this->getSymbolWord($options['min']));
        }

        if (key_exists('max', $options) === true && $length > $options['max']) {
            throw new ValidationException('Максимальная длина строки ' . $options['max'] . ' ' . $this->getSymbolWord($options['max']));
        }
    }

    private function getSymbolWord(int $count): string
    {
        $forms = ['символ', 'символа', 'символов'];
        $mod100 = $count % 100;
        $mod10 = $count % 10;

        if ($mod100 >= 11 && $mod100 <= 19) {
            return $forms[2];
        }

        if ($mod10 === 1) {
            return $forms[0];
        }

        if ($mod10 >= 2 && $mod10 <= 4) {
            return $forms[1];
        }

        return $forms[2];
    }
}
