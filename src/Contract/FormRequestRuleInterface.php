<?php

namespace Konarsky\Contract;

use Konarsky\Exception\Form\ValidationException;

interface FormRequestRuleInterface
{
    /**
     * @param mixed $value
     * @param array $options
     * @return void
     * @throws ValidationException
     */
    public function validate(mixed $value, array $options): void;
}
