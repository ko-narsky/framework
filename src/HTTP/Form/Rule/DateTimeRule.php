<?php

namespace Konarsky\HTTP\Form\Rule;

use DateTime;
use Konarsky\Contract\FormRequestRuleInterface;
use Konarsky\Exception\Form\ValidationException;
use Throwable;

final readonly class DateTimeRule implements FormRequestRuleInterface
{

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, array $options): void
    {
        try {
            new DateTime($value);
        } catch (Throwable) {
            throw new ValidationException('Значение должно соответствовать типу Дата и время');

        }
    }
}
