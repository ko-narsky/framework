<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class RequiredRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, array $options): void
    {
        if ($value === null) {
            $this->throwException();
        }

        if (empty($value) === true && is_numeric($value) === false) {
            $this->throwException();
        }

        if (is_string($value) === true && trim($value) === '') {
            $this->throwException();
        }
    }

    private function throwException(): never
    {
        throw new ValidationException('Обязательное значение');
    }
}
