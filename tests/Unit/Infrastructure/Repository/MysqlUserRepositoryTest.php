<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Repository;

use App\Domain\User\Email;
use App\Domain\User\User;
use App\Domain\User\UserAlreadyExist;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Domain\User\UserNotFound;
use App\Infrastructure\Repository\MysqlUserRepository;
use App\Tests\Unit\Fake\SqliteDatabase;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;

final class MysqlUserRepositoryTest extends Unit
{
    private MysqlUserRepository $repository;

    protected function _before(): void
    {
        $this->repository = new MysqlUserRepository(SqliteDatabase::create());
    }

    public function testCreatesAndFindsUser(): void
    {
        $user = $this->user('user@example.com');

        $this->repository->createUser($user);

        self::assertSame($user->toArray(), $this->repository->getUserById($user->id)->toArray());
        self::assertSame($user->toArray(), $this->repository->getUserByEmail(new Email('user@example.com'))->toArray());
    }

    public function testRejectsDuplicateEmail(): void
    {
        $this->repository->createUser($this->user('user@example.com'));

        $this->expectException(UserAlreadyExist::class);

        $this->repository->createUser($this->user('user@example.com'));
    }

    public function testUpdatesUser(): void
    {
        $user = $this->user('user@example.com');
        $this->repository->createUser($user);
        $changed = User::fromArray(['name' => 'Jane'] + $user->toArray());

        $this->repository->updateUser($changed);

        self::assertSame('Jane', $this->repository->getUserById($user->id)->name->toString());
    }

    public function testThrowsWhenIdNotFound(): void
    {
        $this->expectException(UserNotFound::class);

        $this->repository->getUserById(new UserId(Uuid::uuid7()));
    }

    public function testThrowsWhenEmailNotFound(): void
    {
        $this->expectException(UserNotFound::class);

        $this->repository->getUserByEmail(new Email('nobody@example.com'));
    }

    private function user(string $email): User
    {
        return new User(new UserId(Uuid::uuid7()), new Email($email), new UserName('John'), 'hash');
    }
}
