<?php

declare(strict_types=1);

namespace App\Infrastructure\Token;

use App\Domain\Token\AccessToken;
use App\Domain\Token\AccessTokenGeneratorInterface;
use App\Domain\User\UserId;
use Psr\Clock\ClockInterface;
use Random\RandomException;
use Yiisoft\Auth\Jwt\TokenFactoryInterface;

final readonly class JwtAccessTokenGenerator implements AccessTokenGeneratorInterface
{
    public function __construct(
        private TokenFactoryInterface $tokenFactory,
        private ClockInterface $clock,
        private int $ttl,
    ) {}

    /**
     * @throws RandomException
     */
    public function generate(UserId $userId): AccessToken
    {
        $now = $this->clock->now()->getTimestamp();

        $token = $this->tokenFactory->create([
            'sub' => $userId->toString(),
            'iat' => $now,
            'exp' => $now + $this->ttl,
            'jti' => bin2hex(random_bytes(16)),
        ], 'jws_compact');

        return new AccessToken($token, $this->ttl);
    }
}
