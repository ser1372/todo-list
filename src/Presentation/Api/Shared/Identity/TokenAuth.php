<?php

declare(strict_types=1);

namespace App\Presentation\Api\Shared\Identity;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\AuthenticatorInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\Method\HttpBearer;
use Yiisoft\User\CurrentUser;

final readonly class TokenAuth implements AuthenticatorInterface
{
    public function __construct(
        private HttpBearer $httpBearer,
        private CurrentUser $currentUser,
    ) {}

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        $identity = $this->httpBearer->authenticate($request);
        if ($identity !== null) {
            $this->currentUser->overrideIdentity($identity);
        }

        return $identity;
    }
}
