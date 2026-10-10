<?php

declare(strict_types=1);

namespace App\Application\User\Logout;

use App\Domain\Token\RefreshTokenRepositoryInterface;
use App\Domain\Token\TokenHasherInterface;
use Psr\Clock\ClockInterface;

final readonly class Handler
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private TokenHasherInterface $tokenHasher,
        private ClockInterface $clock,
    ) {}

    public function handle(Command $command): void
    {
        $token = $this->refreshTokenRepository->findByHash($this->tokenHasher->hash($command->refreshToken));
        if (null === $token) {
            return;
        }

        $token->revoke($this->clock->now());
        $this->refreshTokenRepository->revoke($token);
    }
}
