<?php

declare(strict_types=1);

namespace Konarsky\HTTP\OpenApiValidator;

use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use Psr\Http\Message\MessageInterface;
use League\OpenAPIValidation\PSR7\Validators\BodyValidator\UnipartValidator as UnipartValidatorPsr;

class UnipartValidator extends UnipartValidatorPsr
{
    public function validate(OperationAddress $addr, MessageInterface $message): void
    {
        if (preg_match('#^application/.*json$#', $this->contentType)) {
            $body = $message->getParsedBody();
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw InvalidBody::becauseBodyIsNotValidJson(json_last_error_msg(), $addr);
            }
        } else {
            $body = (string) $message->getParsedBody();
        }

        $validator = new SchemaValidator($this->detectValidationStrategy($message));
        $schema    = $this->mediaTypeSpec->schema;
        try {
            $validator->validate($body, $schema);
        } catch (SchemaMismatch $e) {
            throw InvalidBody::becauseBodyDoesNotMatchSchema($this->contentType, $addr, $e);
        }
    }
}