<?php

declare(strict_types=1);

namespace App\Domain\User;

interface UserRepositoryInterface
{
    public function getUserByEmail(Email $email): User;

    public function updateUser(User $user): void;

    /**
     * @param User $user
     * @throws UserAlreadyExist
     * @return void
     */
    public function createUser(User $user): void;

    /**
     * @param UserId $id
     * @throws UserNotFound
     * @return User
     */
    public function getUserById(UserId $id): User;
}
