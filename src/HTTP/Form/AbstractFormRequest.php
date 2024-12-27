<?php

declare(strict_types=1);

namespace Konarsky\HTTP\Form;

use Exception;
use Konarsky\Contract\FormRequestInterface;
use Konarsky\Exception\Form\ValidationException;

abstract class AbstractFormRequest implements FormRequestInterface
{
    protected array $rules = [];
    protected array $errors = [];
    protected bool $skipEmptyValues = false;

   public function __construct(
       protected array $values
   ) {
       foreach ($this->rules() as $rule) {
           $this->addRule($rule[0], $rule[1]);
       }
   }

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
     * @param array $attributes
     * @param array $rule
     *
     * @return void Пример:
     * Пример:
     * $form->addRule(['name'], ['required']);
     * ['maxLength' => 10]
     */
    public function addRule(array $attributes, array $rule): void
    {
        foreach ($attributes as $attribute) {
            $this->rules[$attribute][] = $rule;
        }
    }

    /**
     * @throws Exception
     * @throws ValidationException
     */
    public function validate(): void
    {
        foreach ($this->rules as $attribute => $rules) {
            $value = $this->values[$attribute] ?? null;

            if ($this->skipEmptyValues === true && empty($value) === true && is_numeric($value) === false) {
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
     * @throws Exception
     */
    private function validateAttribute(string $attribute, mixed $value, array $rules): void
    {
        foreach ($rules as $rule) {
            $ruleName = array_is_list($rule) === true ? current($rule) : key($rule);
            $ruleNamespace = __NAMESPACE__ . '\\Rule\\' . ucfirst($ruleName) . 'Rule';
            $ruleOptions = count($rule) > 1 ? $rule[1] : [];

            if (class_exists($ruleNamespace) === false) {
                throw new Exception('Правила валидации ' . $ruleName . ' не существует');
            }

            try {
                (new $ruleNamespace())->validate($value, $ruleOptions);
            } catch (ValidationException $e) {
                $this->addError($attribute, $e->getMessage());
            }
        }
    }
}
