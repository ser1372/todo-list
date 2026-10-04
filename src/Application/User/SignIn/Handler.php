<?php

declare(strict_types=1);

namespace App\Application\User\SignIn;

use App\Domain\User\AuthKeyGeneratorInterface;
use App\Domain\User\PasswordHasherInterface;
use App\Domain\User\User;
use App\Domain\User\UserNotFound;
use App\Domain\User\UserRepositoryInterface;

final readonly class Handler
{
    public function __construct(
        private PasswordHasherInterface $passwordHasher,
        private UserRepositoryInterface $userRepository,
        private AuthKeyGeneratorInterface $authKeyGenerator,
    ) {}

    public function handle(Command $command): ?User
    {
        try {
            $user = $this->userRepository->getUserByEmail($command->email);

            if(!$user->isValidPassword($command->password, $this->passwordHasher)) {
                return null;
            }

            $user->generateAuthKey($this->authKeyGenerator);
            $this->userRepository->updateUser($user);

            return $user;
        } catch (UserNotFound) {}

        return null;
    }
}
