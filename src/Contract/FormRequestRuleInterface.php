<?php

namespace Konarsky\Contract;

use Konarsky\Exception\Form\RequiredValidationException;

interface FormRequestRuleInterface
{
    /**
     * @param mixed $value
     * @param array $options
     * @return void
     * @throws RequiredValidationException
     */
    public function validate(mixed $value, array $options): void;
}
