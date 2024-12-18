<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class MinLengthRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, mixed $options): void
    {
        if (is_string($value) === false) {
            throw new ValidationException('Значение должно быть строкой');
        }

        if (mb_strlen($value) < $options) {
            throw new ValidationException('Минимальная длина строки ' . $options . ' ' . $this->getSymbolWord($options));
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
