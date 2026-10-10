<?php

namespace App\Application\User\SignUp;

use App\Domain\Token\RefreshTokenRepositoryInterface;
use App\Domain\Token\TokenPair;
use App\Domain\User\PasswordHasherInterface;
use App\Domain\User\User;
use App\Domain\User\UserAlreadyExist;
use App\Domain\User\UserId;
use App\Domain\User\UserRepositoryInterface;
use Psr\Clock\ClockInterface;
use Ramsey\Uuid\Uuid;

final readonly class Handler
{
    public function __construct(
        private PasswordHasherInterface   $passwordHasher,
        private UserRepositoryInterface   $userRepository,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private ClockInterface            $clock,
    )
    {}

    public function handle(Command $command): ?TokenPair
    {
        $user = new User(
            new UserId(Uuid::uuid7()),
            $command->email,
            $command->name,
            $this->passwordHasher->hash($command->password),
        );

        try {
            $this->userRepository->createUser($user);
        } catch (UserAlreadyExist) {
            return null;
        }

        return $this->refreshTokenRepository->create($user, $this->clock->now());
    }
}
