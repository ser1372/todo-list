<?php

declare(strict_types=1);

use App\Presentation\Api\Shared\Identity\IdentityRepository;
use App\Presentation\Api\Shared\Identity\TokenAuth;
use Yiisoft\Auth\AuthenticatorInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Auth\IdentityWithTokenRepositoryInterface;

return [
    IdentityRepositoryInterface::class => IdentityRepository::class,
    IdentityWithTokenRepositoryInterface::class => IdentityRepository::class,
    AuthenticatorInterface::class => TokenAuth::class,
];
