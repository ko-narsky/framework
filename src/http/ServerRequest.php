<?php

namespace Konarsky\http;

use Konarsky\http\enum\ContentTypes;
use Konarsky\http\exception\BadRequestHttpException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    private array $attributes = [];
    private array $cookieParams = [];
    private array $parsedBody;
    private array $queryParams = [];
    private array $uploadedFiles = [];
    public function __construct(
        string $method,
        UriInterface $uri,
        array $headers = [],
        StreamInterface $body = null,
        string $protocolVersion = '1.1',
        private array $serverParams = []
    ) {
        parent::__construct($method, $uri, $headers, $body, $protocolVersion);

        $this->setQueryParamsFormString($uri->getQuery());
        $this->setParsedBody();
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;

        return $clone;
    }

    public function setQueryParamsFormString($queryParams): void
    {
        if ($queryParams === '') {
            return;
        }

        $queryGroupParams = explode('&', $queryParams);
        foreach ($queryGroupParams as $queryParam) {
            $param = explode('=', $queryParam);
            $this->queryParams[urldecode($param[0])] = urldecode($param[1]);
        }
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->queryParams = $query;

        return $clone;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;

        return $clone;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->parsedBody = $data;

        return $clone;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, $default = null)
    {
        if (array_key_exists($name, $this->attributes) === false) {
            return $default;
        }

        return $this->attributes[$name];
    }

    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    public function withoutAttribute(string $name): ServerRequestInterface
    {
        if (array_key_exists($name, $this->attributes) === false) {
            return $this;
        }

        $clone = clone $this;
        unset($clone->attributes[$name]);

        return $clone;
    }

    private function setParsedBody(): void
    {
        $this->parsedBody = [];

        $contentType = $this->getHeader('Content-Type')[0] ?? null;

        if ($contentType === null) {
            return;
        }

        if ($contentType === ContentTypes::APPLICATION_JSON->value) {
            $this->parsedBody = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new BadRequestHttpException('Ошибка при декодировании JSON: ' . json_last_error_msg());
            }

            return;
        }

        if ($contentType === ContentTypes::APPLICATION_X_WWW_FORM_URLENCODED->value) {
            $this->parsedBody = $_POST;

            return;
        }

        if ($contentType === ContentTypes::MULTIPART_FORM_DATA->value) {
            $this->parsedBody = $_POST;

            return;
        }

        throw new BadRequestHttpException("Неподдерживаемый тип Content-Type: $contentType");
    }
}