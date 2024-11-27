<?php

namespace Konarsky\HTTP\Form\Rules;

use Konarsky\Contracts\FormRequestRuleInterface;
use Konarsky\HTTP\Form\AbstractFormRequest;

readonly class RequiredFormRequestRule implements FormRequestRuleInterface
{
    public function __construct(
        private AbstractFormRequest $formRequest
    ) {
    }
    public function validate(string $attribute, mixed $value): void
    {
        if (empty($value) === true) {
            $this->formRequest->addError($attribute, 'обязательно');
        }
    }
}
