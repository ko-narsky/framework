<?php

declare(strict_types=1);

namespace Konarsky\HTTP\Factory;

use Konarsky\Exception\HTTP\BadRequestHttpException;
use Konarsky\HTTP\Enum\ContentTypes;
use Konarsky\HTTP\ServerRequest;
use Konarsky\HTTP\Uri;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestFactory
{
    /**
     * @return ServerRequestInterface
     *
     * @throws BadRequestHttpException
     */
    public function create(): ServerRequestInterface
    {
        $instance = new ServerRequest(
            $_SERVER['REQUEST_METHOD'],
            new Uri($_SERVER['REQUEST_URI']),
            $this->getHeaders()
        );

        $instance = $instance->withParsedBody($this->getParsedBody($instance));

        return $instance->withQueryParams($this->getQueryParams($instance));
    }

    /**
     * @return array
     */
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

    /**
     * @param ServerRequestInterface $instance
     *
     * @return array
     *
     * @throws BadRequestHttpException
     */
    private function getParsedBody(ServerRequestInterface $instance): array
    {
        $contentType = $instance->getHeader('Content-Type')[0] ?? null;

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

    private function getQueryParams(ServerRequestInterface $instance): array
    {
        $query = $instance->getUri()->getQuery();
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
