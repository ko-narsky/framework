<?php

declare(strict_types=1);

namespace Konarsky\HTTP\Factory;

use InvalidArgumentException;
use Konarsky\Contract\FormRequestFactoryInterface;
use Konarsky\Contract\FormRequestInterface;
use LogicException;

class FormRequestFactory implements FormRequestFactoryInterface
{

    /**
     * @inheritDoc
     */
    public function create(string $formClassName): FormRequestInterface
    {
        if (class_exists($formClassName) === false) {
            throw new InvalidArgumentException("Класс формы $formClassName не существует");
        }

        $formInstance = new $formClassName();

        if ($formInstance instanceof FormRequestInterface === false) {
            throw new LogicException("Класс $formClassName должен реализовывать FormRequestInterface");
        }

        return $formInstance;
    }
}
