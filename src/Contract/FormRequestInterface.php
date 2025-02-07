<?php

namespace Konarsky\Contract;

interface FormRequestInterface
{
    /**
     * @return array
     */
    public function rules(): array;

    /**
     * @param array $attributes
     * @param array $rule
     *
     * @return void
     */
    public function addRule(array $attributes, array $rule): void;

    /**
     * @return void
     */
    public function validate(): void;

    /**
     * @param string $attribute
     * @param string $message
     *
     * @return void
     */
    public function addError(string $attribute, string $message): void;

    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * @return void
     */
    public function setSkipEmptyValues(): void;

    /**
     * @return array
     */
    public function getValues(): array;
}
