<?php

declare(strict_types=1);

namespace App\Presentation\Api\Shared\Identity;

use Jose\Component\Checker\ClaimExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\AuthenticatorInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\Jwt\JwtMethod;
use Yiisoft\User\CurrentUser;

final readonly class JwtAuth implements AuthenticatorInterface
{
    public function __construct(
        private JwtMethod $jwtMethod,
        private CurrentUser $currentUser,
    ) {}

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        try {
            $identity = $this->jwtMethod->authenticate($request);
        } catch (ClaimExceptionInterface) {
            return null;
        }

        if (null !== $identity) {
            $this->currentUser->overrideIdentity($identity);
        }

        return $identity;
    }
}
