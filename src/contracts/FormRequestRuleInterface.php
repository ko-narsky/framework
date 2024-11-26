<?php

namespace Konarsky\contracts;

interface FormRequestRuleInterface
{
    /**
     * @param string $attribute
     * @param mixed $value
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value): void;
}
