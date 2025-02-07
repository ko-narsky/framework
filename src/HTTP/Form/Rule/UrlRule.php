<?php

namespace Konarsky\HTTP\Form\Rule;

use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;

final readonly class UrlRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, array $options): void
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            throw new ValidationException('Неверный формат URL');
        }
    }
}
