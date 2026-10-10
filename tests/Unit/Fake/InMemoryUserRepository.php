<?php

declare(strict_types=1);

namespace App\Tests\Unit\Fake;

use App\Domain\User\Email;
use App\Domain\User\User;
use App\Domain\User\UserAlreadyExist;
use App\Domain\User\UserId;
use App\Domain\User\UserNotFound;
use App\Domain\User\UserRepositoryInterface;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var array<string, array> */
    private array $rows = [];

    public function getUserByEmail(Email $email): User
    {
        foreach ($this->rows as $row) {
            if ($email->toString() === $row['email']) {
                return User::fromArray($row);
            }
        }
        throw new UserNotFound();
    }

    public function updateUser(User $user): void
    {
        $this->rows[$user->id->toString()] = $user->toArray();
    }

    public function createUser(User $user): void
    {
        foreach ($this->rows as $row) {
            if ($user->email->toString() === $row['email']) {
                throw new UserAlreadyExist();
            }
        }
        $this->rows[$user->id->toString()] = $user->toArray();
    }

    public function getUserById(UserId $id): User
    {
        if (!isset($this->rows[$id->toString()])) {
            throw new UserNotFound();
        }
        return User::fromArray($this->rows[$id->toString()]);
    }

    public function delete(UserId $id): void
    {
        unset($this->rows[$id->toString()]);
    }

    public function count(): int
    {
        return count($this->rows);
    }
}
