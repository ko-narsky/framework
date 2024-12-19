<?php

declare(strict_types=1);

namespace Konarsky\HTTP\Form;

use Konarsky\Contract\FormRequestInterface;
use Konarsky\Exception\Form\ValidationException;
use Konarsky\Exception\HTTP\BadRequestHttpException;

abstract class AbstractFormRequest implements FormRequestInterface
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
        return $this->rules;
    }

    /**
     * Динамическая установка правил валидации
     *
     * @param array $attributes
     * @param array|string $rule
     *
     * @return array
     * Пример:
     * $form->addRule(['name'], 'required');
     */
    public function addRule(array $attributes, array|string $rule): array // TODO изменил array|string
    {
        foreach ($attributes as $attribute) {
            $this->rules[$attribute][] = is_string($rule) === true ? [$rule] : $rule;
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

            $this->validateAttribute($attribute, $value, $rules);
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

    /**
     * @param string $attribute
     * @param mixed $value
     * @param array $rules
     *
     * @return void
     *
     * @throws BadRequestHttpException
     */
    private function validateAttribute(string $attribute, mixed $value, array $rules): void
    {
        foreach ($rules as $rule) {
            $ruleName = array_is_list($rule) === true ? current($rule) : key($rule);
            $ruleOptions = array_is_list($rule) === true ? null : current($rule);
            $ruleNamespace = __NAMESPACE__ . '\\Rule\\' . ucfirst($ruleName) . 'Rule';

            if (class_exists($ruleNamespace) === false) {
                throw new BadRequestHttpException('Правила валидации ' . $ruleName . ' не существует');
            }

            try {
                (new $ruleNamespace())->validate($value, $ruleOptions);
            } catch (ValidationException $e) {
                $this->addError($attribute, $e->getMessage());
            }
        }
    }
}
