<?php

declare(strict_types=1);

namespace App\Tests\Unit\Fake;

use App\Domain\User\Password;
use App\Domain\User\PasswordHasherInterface;

final class PlainPasswordHasher implements PasswordHasherInterface
{
    public function hash(Password $password): string
    {
        return 'hashed:' . $password->toString();
    }

    public function validate(Password $password, string $hash): bool
    {
        return $this->hash($password) === $hash;
    }
}
