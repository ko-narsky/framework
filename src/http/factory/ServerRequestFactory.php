<?php

declare(strict_types=1);

namespace Konarsky\http\factory;

use Konarsky\http\enum\ContentTypes;
use Konarsky\http\exception\BadRequestHttpException;
use Konarsky\http\ServerRequest;
use Konarsky\http\Uri;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestFactory
{
    private ServerRequestInterface $instance;
    public function create(): ServerRequestInterface
    {
        $this->instance = new ServerRequest(
            $_SERVER['REQUEST_METHOD'],
            new Uri($_SERVER['REQUEST_URI']),
            $this->getHeaders()
        );

        $this->instance = $this->instance->withParsedBody($this->getParsedBody());
        $this->instance = $this->instance->withQueryParams($this->getQueryParams());

        return $this->instance;
    }

    private function getHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (preg_match('/^HTTP_/', $name)) {
                $name = strtr(substr($name, 5), '_', ' ');
                $name = ucwords(strtolower($name));
                $name = strtr($name, ' ', '-');
                $headers[$name][] = $value;
            }
        }

        return $headers;
    }

    private function getParsedBody(): array
    {
        $contentType = $this->instance->getHeader('Content-Type')[0] ?? null;

        if ($contentType === null) {
            return [];
        }

        if ($contentType === ContentTypes::APPLICATION_JSON->value) {
            $parsedBody = json_decode(file_get_contents('php://input'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new BadRequestHttpException('Ошибка при декодировании JSON: ' . json_last_error_msg());
            }

            return $parsedBody;
        }

        if ($contentType === ContentTypes::APPLICATION_X_WWW_FORM_URLENCODED->value) {
            return $_POST;
        }

        if ($contentType === ContentTypes::MULTIPART_FORM_DATA->value) {
            return $_POST;

        }

        throw new BadRequestHttpException("Неподдерживаемый тип Content-Type: $contentType");
    }

    private function getQueryParams(): array
    {
        $query = $this->instance->getUri()->getQuery();
        $queryParams = [];

        if ($query === '') {
            return $queryParams;
        }

        $queryGroupParams = explode('&', $query);

        foreach ($queryGroupParams as $queryParam) {
            $param = explode('=', $queryParam);
            $queryParams[urldecode($param[0])] = urldecode($param[1]);
        }

        return $queryParams;
    }
}
