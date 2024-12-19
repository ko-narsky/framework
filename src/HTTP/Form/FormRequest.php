<?php

namespace Konarsky\HTTP\Form;

use Psr\Http\Message\ServerRequestInterface;

class FormRequest extends AbstractFormRequest
{
    public function __construct(readonly ServerRequestInterface $request)
    {
        $this->values = $request->getParsedBody();
    }
}
