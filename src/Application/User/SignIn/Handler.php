<?php

declare(strict_types=1);

namespace App\Application\User\SignIn;

use App\Domain\Token\RefreshTokenRepositoryInterface;
use App\Domain\Token\TokenPair;
use App\Domain\User\PasswordHasherInterface;
use App\Domain\User\UserNotFound;
use App\Domain\User\UserRepositoryInterface;
use Psr\Clock\ClockInterface;

final readonly class Handler
{
    public function __construct(
        private PasswordHasherInterface $passwordHasher,
        private UserRepositoryInterface $userRepository,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private ClockInterface $clock,
    ) {}

    public function handle(Command $command): ?TokenPair
    {
        try {
            $user = $this->userRepository->getUserByEmail($command->email);
        } catch (UserNotFound) {
            return null;
        }

        if (!$user->isValidPassword($command->password, $this->passwordHasher)) {
            return null;
        }

        return $this->refreshTokenRepository->create($user, $this->clock->now());
    }
}
