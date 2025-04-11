<?php

namespace Konarsky\Test;

use Konarsky\Contract\AuthenticationMiddlewareInterface;
use Konarsky\Contract\UsersRepositoryInterface;
use Konarsky\Exception\HTTP\UnauthorizedHttpException;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class DummyAuthenticationMiddleware implements AuthenticationMiddlewareInterface
{
    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly UsersRepositoryInterface $usersRepository,
    ) {}
    public function __invoke(ServerRequestInterface $request)
    {

        $authHeader = $this->request->getHeader('Authorization');

        if (empty($authHeader) === true) {
            throw new UnauthorizedHttpException();
        }

        try {
            if ($this->usersRepository->isExistBy('email', $authHeader[0]) === false) {
                throw new UnauthorizedHttpException();
            }
        } catch (Throwable) {
            throw new UnauthorizedHttpException();
        }
    }
}