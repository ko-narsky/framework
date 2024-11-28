<?php

declare(strict_types=1);

namespace Konarsky\HTTP\Form;

use Konarsky\Contract\FormRequestInterface;
use Konarsky\Exception\HTTP\BadRequestHttpException;
use Konarsky\HTTP\Enum\FormRequestRulesEnum;

class AbstractFormRequest implements FormRequestInterface
{
    protected array $rules = [];
    protected array $errors = [];
    protected array $values = [];
    protected bool $skipEmptyValues = false;

    /**
     * Возврат правил валидации формы
     *
     * @return array
     * Пример:
     * [
     *     [['name'], 'required'],
     *     [['name'], 'string'],
     * ]
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Динамическая установка правил валидации
     *
     * @param  array $attributes
     * @param  array $rule
     * @return array
     * Пример:
     * $form->addRule(['name'], 'required');
     */
    public function addRule(array $attributes, array $rule): array
    {
        foreach ($attributes as $attribute) {
            $this->rules[$attribute][] = $rule;
        }

        return $this->rules;
    }

    /**
     * @throws BadRequestHttpException
     */
    public function validate(): void
    {
        foreach ($this->rules as $attribute => $rules) {
            $value = $this->values[$attribute] ?? null;

            if ($this->skipEmptyValues === true && $value === null) {
                continue;
            }

            foreach ($rules as $rule) {
                (new (FormRequestRulesEnum::match($rule)))->validate($attribute, $this->values[$attribute]);
            }
        }
    }

    public function addError(string $attribute, string $message): void
    {
        $this->errors[$attribute][] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setSkipEmptyValues(): void
    {
        $this->skipEmptyValues = true;
    }

    /**
     * Возврат значений формы
     *
     * @return array
     * Пример:
     * [
     *     "id" => 1,
     *     "order_id" => 3,
     *     "name" => "Некоторое имя 1"
     * ]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
