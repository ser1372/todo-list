<?php

declare(strict_types=1);

use App\Presentation\Api\Shared\Identity\AuthenticationFailureHandler;
use App\Presentation\Api\Shared\Identity\IdentityRepository;
use App\Presentation\Api\Shared\Identity\JwtAuth;
use Yiisoft\Auth\AuthenticatorInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Definitions\Reference;

return [
    IdentityRepositoryInterface::class => IdentityRepository::class,
    AuthenticatorInterface::class => JwtAuth::class,
    Authentication::class => [
        '__construct()' => [
            'authenticationFailureHandler' => Reference::to(AuthenticationFailureHandler::class),
        ],
    ],
];
