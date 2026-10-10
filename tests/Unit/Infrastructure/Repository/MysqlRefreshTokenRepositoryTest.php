<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Repository;

use App\Domain\User\Email;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Infrastructure\Repository\MysqlRefreshTokenRepository;
use App\Infrastructure\Repository\MysqlUserRepository;
use App\Tests\Unit\Fake\FakeAccessTokenGenerator;
use App\Tests\Unit\Fake\SqliteDatabase;
use App\Infrastructure\Token\Sha256TokenHasher;
use Codeception\Test\Unit;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;

final class MysqlRefreshTokenRepositoryTest extends Unit
{
    private MysqlRefreshTokenRepository $repository;
    private MysqlUserRepository $users;
    private DateTimeInterface $now;

    protected function _before(): void
    {
        $db = SqliteDatabase::create();
        $this->repository = new MysqlRefreshTokenRepository($db, new FakeAccessTokenGenerator(), new Sha256TokenHasher(), 3600, new NullLogger());
        $this->users = new MysqlUserRepository($db);
        $this->now = new DateTimeImmutable('@1700000000');
    }

    public function testCreatePersistsTokenAndReturnsPair(): void
    {
        $user = $this->user('user@example.com');

        $pair = $this->repository->create($user, $this->now);

        $stored = $this->repository->findByHash(new Sha256TokenHasher()->hash($pair->refreshToken));
        self::assertSame('access-' . $user->id->toString(), $pair->accessToken->token);
        self::assertSame(3600, $pair->refreshExpiresIn);
        self::assertTrue($stored->userId->isEqualTo($user->id));
        self::assertSame(1700003600, $stored->expiresAt->getTimestamp());
        self::assertNull($stored->revokedAt);
    }

    public function testFindByHashReturnsNullForUnknownHash(): void
    {
        self::assertNull($this->repository->findByHash(new Sha256TokenHasher()->hash('unknown')));
    }

    public function testRevokeSucceedsOnlyOnce(): void
    {
        $pair = $this->repository->create($this->user('user@example.com'), $this->now);
        $first = $this->repository->findByHash(new Sha256TokenHasher()->hash($pair->refreshToken));
        $second = $this->repository->findByHash(new Sha256TokenHasher()->hash($pair->refreshToken));
        $first->revoke($this->now);
        $second->revoke($this->now);

        self::assertTrue($this->repository->revoke($first));
        self::assertFalse($this->repository->revoke($second));
        self::assertSame(
            1700000000,
            $this->repository->findByHash(new Sha256TokenHasher()->hash($pair->refreshToken))->revokedAt?->getTimestamp(),
        );
    }

    public function testRevokeAllForUserTouchesOnlyThatUser(): void
    {
        $user = $this->user('user@example.com');
        $other = $this->user('other@example.com');
        $first = $this->repository->create($user, $this->now);
        $second = $this->repository->create($user, $this->now);
        $foreign = $this->repository->create($other, $this->now);

        $this->repository->revokeAllForUser($user->id, $this->now);

        self::assertNotNull($this->repository->findByHash(new Sha256TokenHasher()->hash($first->refreshToken))->revokedAt);
        self::assertNotNull($this->repository->findByHash(new Sha256TokenHasher()->hash($second->refreshToken))->revokedAt);
        self::assertNull($this->repository->findByHash(new Sha256TokenHasher()->hash($foreign->refreshToken))->revokedAt);
    }

    private function user(string $email): User
    {
        $user = new User(new UserId(Uuid::uuid7()), new Email($email), new UserName('John'), 'hash');
        $this->users->createUser($user);

        return $user;
    }
}
