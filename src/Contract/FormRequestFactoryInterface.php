<?php

namespace Konarsky\Contract;

interface FormRequestFactoryInterface
{
    /**
     * @param string $formClassName
     *
     * @return FormRequestInterface
     */
    public function create(string $formClassName): FormRequestInterface;
}
