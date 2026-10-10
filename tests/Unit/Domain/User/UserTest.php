<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User;

use App\Domain\User\Email;
use App\Domain\User\Password;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Tests\Unit\Fake\PlainPasswordHasher;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;

final class UserTest extends Unit
{
    public function testValidatesPassword(): void
    {
        $hasher = new PlainPasswordHasher();
        $user = $this->createUser($hasher->hash(new Password('secret123')));

        self::assertTrue($user->isValidPassword(new Password('secret123'), $hasher));
        self::assertFalse($user->isValidPassword(new Password('wrongpass'), $hasher));
    }

    public function testChangesPassword(): void
    {
        $hasher = new PlainPasswordHasher();
        $user = $this->createUser($hasher->hash(new Password('secret123')));

        $user->changePassword(new Password('newsecret'), $hasher);

        self::assertTrue($user->isValidPassword(new Password('newsecret'), $hasher));
        self::assertFalse($user->isValidPassword(new Password('secret123'), $hasher));
    }

    public function testArrayRoundTrip(): void
    {
        $user = $this->createUser('hash');

        $data = $user->toArray();
        $restored = User::fromArray($data);

        self::assertSame([
            'id' => $user->id->toString(),
            'email' => 'user@example.com',
            'name' => 'John',
            'password_hash' => 'hash',
        ], $data);
        self::assertSame($data, $restored->toArray());
    }

    private function createUser(string $passwordHash): User
    {
        return new User(
            new UserId(Uuid::uuid7()),
            new Email('user@example.com'),
            new UserName('John'),
            $passwordHash,
        );
    }
}
