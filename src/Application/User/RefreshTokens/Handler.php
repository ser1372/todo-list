<?php

declare(strict_types=1);

namespace App\Application\User\RefreshTokens;

use App\Domain\Token\InvalidRefreshToken;
use App\Domain\Token\RefreshTokenRepositoryInterface;
use App\Domain\Token\TokenHasherInterface;
use App\Domain\Token\TokenPair;
use App\Domain\User\UserNotFound;
use App\Domain\User\UserRepositoryInterface;
use Psr\Clock\ClockInterface;

final readonly class Handler
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private TokenHasherInterface $tokenHasher,
        private UserRepositoryInterface $userRepository,
        private ClockInterface $clock,
    ) {}

    /**
     * @throws InvalidRefreshToken
     */
    public function handle(Command $command): TokenPair
    {
        $now = $this->clock->now();
        $token = $this->refreshTokenRepository->findByHash(
            $this->tokenHasher->hash($command->refreshToken)
        );

        if (null === $token || $token->isExpired($now)) {
            throw new InvalidRefreshToken();
        }

        $token->revoke($now);
        if (!$this->refreshTokenRepository->revoke($token)) {
            $this->refreshTokenRepository->revokeAllForUser($token->userId, $now);
            throw new InvalidRefreshToken();
        }

        try {
            $user = $this->userRepository->getUserById($token->userId);
        } catch (UserNotFound) {
            throw new InvalidRefreshToken();
        }

        return $this->refreshTokenRepository->create($user, $now);
    }
}
