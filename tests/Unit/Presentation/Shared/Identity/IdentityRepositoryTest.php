<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Shared\Identity;

use App\Domain\User\Email;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Presentation\Api\Shared\Identity\IdentityRepository;
use App\Tests\Unit\Fake\InMemoryUserRepository;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;

final class IdentityRepositoryTest extends Unit
{
    private InMemoryUserRepository $users;
    private IdentityRepository $repository;

    protected function _before(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->repository = new IdentityRepository($this->users);
    }

    public function testFindsExistingUser(): void
    {
        $user = new User(new UserId(Uuid::uuid7()), new Email('user@example.com'), new UserName('John'), 'hash');
        $this->users->createUser($user);

        self::assertSame($user->id->toString(), $this->repository->findIdentity($user->id->toString())?->getId());
    }

    public function testReturnsNullForUnknownUser(): void
    {
        self::assertNull($this->repository->findIdentity(Uuid::uuid7()->toString()));
    }

    public function testReturnsNullForInvalidId(): void
    {
        self::assertNull($this->repository->findIdentity('not-a-uuid'));
    }
}
