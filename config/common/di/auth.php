<?php

declare(strict_types=1);

use App\Domain\Token\AccessTokenGeneratorInterface;
use App\Infrastructure\Token\JwtAccessTokenGenerator;
use App\Infrastructure\Repository\MysqlRefreshTokenRepository;
use Jose\Component\Checker\ExpirationTimeChecker;
use Psr\Clock\ClockInterface;
use Yiisoft\Auth\Jwt\KeyFactory\FromSecret;
use Yiisoft\Auth\Jwt\KeyFactoryInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Auth\Jwt\JwtMethod;
use Yiisoft\Auth\Jwt\TokenRepositoryInterface;

/** @var array $params */

return [
    KeyFactoryInterface::class => static function () use ($params): FromSecret {
        $secret = $params['yiisoft/auth-jwt']['key']['secret'];
        if (strlen($secret) < 32) {
            throw new RuntimeException('JWT_SECRET environment variable must be set to at least 32 bytes.');
        }
        return new FromSecret($secret);
    },
    AccessTokenGeneratorInterface::class => [
        'class' => JwtAccessTokenGenerator::class,
        '__construct()' => [
            'ttl' => $params['app/auth']['accessTtl'],
        ],
    ],
    MysqlRefreshTokenRepository::class => [
        '__construct()' => ['refreshTtl' => $params['app/auth']['refreshTtl']],
    ],
    JwtMethod::class => static fn(
        IdentityRepositoryInterface $identityRepository,
        TokenRepositoryInterface $tokenRepository,
        ClockInterface $clock,
    ) => new JwtMethod(
        $identityRepository,
        $tokenRepository,
        [new ExpirationTimeChecker(clock: $clock)],
    ),
];
