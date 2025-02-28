<?php

declare(strict_types=1);

namespace Konarsky\HTTP\OpenApiValidator;

use Konarsky\HTTP\OpenApiValidator\RequestValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder as ValidatorBuilderPsr;

class ValidatorBuilder extends ValidatorBuilderPsr
{
    public function getRequestValidator(): RequestValidator
    {
        $schema = $this->getOrCreateSchema();

        return new RequestValidator($schema);
    }
}