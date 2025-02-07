<?php

declare(strict_types=1);

namespace Konarsky\HTTP\OpenApiValidator;

use cebe\openapi\spec\OpenApi;
use League\OpenAPIValidation\PSR7\RequestValidator as RequestValidatorPsr;
use League\OpenAPIValidation\PSR7\SpecFinder;
use League\OpenAPIValidation\PSR7\Validators\CookiesValidator\CookiesValidator;
use League\OpenAPIValidation\PSR7\Validators\HeadersValidator;
use League\OpenAPIValidation\PSR7\Validators\PathValidator;
use League\OpenAPIValidation\PSR7\Validators\QueryArgumentsValidator;
use League\OpenAPIValidation\PSR7\Validators\SecurityValidator;
use League\OpenAPIValidation\PSR7\Validators\ValidatorChain;
use Konarsky\HTTP\OpenApiValidator\BodyValidator;

class RequestValidator extends RequestValidatorPsr
{
    public function __construct(OpenApi $schema)
    {
        $this->openApi   = $schema;
        $finder          = new SpecFinder($this->openApi);
        $this->validator = new ValidatorChain(
            new HeadersValidator($finder),
            new CookiesValidator($finder),
            new BodyValidator($finder),
            new QueryArgumentsValidator($finder),
            new PathValidator($finder),
            new SecurityValidator($finder)
        );
    }
}