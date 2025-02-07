<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class MatchRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, array $options): void
    {
        if (preg_match($options[0], $value) === false) {
            throw new ValidationException('Значение не соответствует формату.');
        }
    }
}
