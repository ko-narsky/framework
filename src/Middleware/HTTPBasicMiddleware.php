<?php

namespace Konarsky\Middleware;

use JetBrains\PhpStorm\NoReturn;
use Konarsky\Contract\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;

class HTTPBasicMiddleware implements MiddlewareInterface
{
    #[NoReturn]
    private function authenticate(): void
    {
        header('WWW-Authenticate: Basic realm="Test Authentication System"');
        header('HTTP/1.0 401 Unauthorized');

        echo "Вы должны ввести корректный логин и пароль для получения доступа к ресурсу \n";

        exit;
    }

    private function isAuthenticate($request): bool
    {
        if  ($request->hasHeader('Authorization') === true) {
            $authorizationHeader = $request->getHeader('Authorization')[0];
        }

        if (
            isset($authorizationHeader) === false
            || str_starts_with($authorizationHeader, 'Basic ') === false
        ) {
            return false;
        }

        $base64Authorization = substr($authorizationHeader, 6);
        $decodedAuthorization = base64_decode($base64Authorization);

        list($username, $password) = explode(':', $decodedAuthorization, 2);

        if ($username === getenv('CLIENT_ID') && $password === getenv('CLIENT_SECRET')) {
            return true;
        }

        return false;
    }

    public function __invoke(RequestInterface $request): void
    {
        while ($this->isAuthenticate($request) === false) {
            $this->authenticate();
        }
    }
}
