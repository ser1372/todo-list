<?php

namespace App\Application\User\SignUp;

use App\Domain\User\AuthKeyGeneratorInterface;
use App\Domain\User\PasswordHasherInterface;
use App\Domain\User\User;
use App\Domain\User\UserAlreadyExist;
use App\Domain\User\UserId;
use App\Domain\User\UserRepositoryInterface;
use Ramsey\Uuid\Uuid;

final readonly class Handler
{
    public function __construct(
        private AuthKeyGeneratorInterface $authKeyGenerator,
        private PasswordHasherInterface   $passwordHasher,
        private UserRepositoryInterface   $userRepository,
    )
    {}

    public function handle(Command $command): ?User
    {
        try {
            $user = new User(
                new UserId(Uuid::uuid7()),
                $command->email,
                $command->name,
                $this->passwordHasher->hash($command->password),
                $this->authKeyGenerator->generate(),
            );

            $this->userRepository->createUser($user);

            return $user;
        } catch (UserAlreadyExist) {}

        return null;
    }
}
