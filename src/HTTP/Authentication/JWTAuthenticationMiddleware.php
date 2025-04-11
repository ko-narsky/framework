<?php

namespace Konarsky\HTTP\Authentication;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Konarsky\Configuration\ConfigurationInterface;
use Konarsky\Contract\AuthenticationMiddlewareInterface;
use Konarsky\Contract\UsersRepositoryInterface;
use Konarsky\Exception\HTTP\UnauthorizedHttpException;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class JWTAuthenticationMiddleware implements AuthenticationMiddlewareInterface
{

    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly ConfigurationInterface $configuration,
        private readonly UsersRepositoryInterface $usersRepository,
    ) {}
    public function __invoke(ServerRequestInterface $request)
    {
        $authHeader = $this->request->getHeader('Authorization');

        if (empty($authHeader) === true || str_starts_with($authHeader[0], 'Bearer ') === false) {
            throw new UnauthorizedHttpException();
        }

        $token = substr($authHeader[0], 7);

        try {
            $decoded = JWT::decode($token, new Key($this->configuration->get('params')['JWT_PUBLIC_KEY'], 'RS256'));

            if ($decoded->exp < time()) {
                throw new UnauthorizedHttpException();
            }

            if ($this->usersRepository->isExistBy('uid', $decoded->sub) === false) {
                throw new UnauthorizedHttpException();
            }
        } catch (Throwable) {
            throw new UnauthorizedHttpException();
        }
    }
}