<?php

declare(strict_types=1);

namespace Konarsky\HTTP\Factories;

use Konarsky\HTTP\Request;
use Konarsky\HTTP\Uri;
use Psr\Http\Message\RequestInterface;

class RequestFactory
{
    /**
     * @return RequestInterface
     */
    public function create(): RequestInterface
    {
        return new Request(
            $_SERVER['REQUEST_METHOD'],
            new Uri($_SERVER['REQUEST_URI']),
            $this->getHeaders()
        );
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
}
