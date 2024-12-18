<?php

namespace Konarsky\Contract;

use Konarsky\Exception\Form\ValidationException;

interface FormRequestRuleInterface
{
    /**
     * @param mixed $value
     *
     * @return void
     *
     * @throws ValidationException
     */
    public function validate(mixed $value, mixed $options): void;
}
