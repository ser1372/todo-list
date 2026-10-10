<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User;

use App\Application\User\Logout\Command;
use App\Application\User\Logout\Handler;
use App\Domain\User\Email;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Tests\Unit\Fake\FrozenClock;
use App\Tests\Unit\Fake\InMemoryRefreshTokenRepository;
use App\Infrastructure\Token\Sha256TokenHasher;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;

final class LogoutHandlerTest extends Unit
{
    private InMemoryRefreshTokenRepository $refreshTokens;
    private FrozenClock $clock;
    private Handler $handler;
    private User $user;

    protected function _before(): void
    {
        $this->clock = new FrozenClock();
        $this->refreshTokens = new InMemoryRefreshTokenRepository();
        $this->handler = new Handler($this->refreshTokens, new Sha256TokenHasher(), $this->clock);
        $this->user = new User(new UserId(Uuid::uuid7()), new Email('user@example.com'), new UserName('John'), 'hash');
    }

    public function testRevokesToken(): void
    {
        $tokens = $this->refreshTokens->create($this->user, $this->clock->now());

        $this->handler->handle(new Command($tokens->refreshToken));

        $stored = $this->refreshTokens->findByHash(new Sha256TokenHasher()->hash($tokens->refreshToken));
        self::assertSame($this->clock->now()->getTimestamp(), $stored->revokedAt?->getTimestamp());
    }

    public function testKeepsOtherSessions(): void
    {
        $tokens = $this->refreshTokens->create($this->user, $this->clock->now());
        $other = $this->refreshTokens->create($this->user, $this->clock->now());

        $this->handler->handle(new Command($tokens->refreshToken));

        self::assertNull($this->refreshTokens->findByHash(new Sha256TokenHasher()->hash($other->refreshToken))->revokedAt);
    }

    public function testIgnoresUnknownToken(): void
    {
        $this->handler->handle(new Command('unknown'));

        self::assertSame(0, $this->refreshTokens->count());
    }

    public function testIsIdempotent(): void
    {
        $tokens = $this->refreshTokens->create($this->user, $this->clock->now());
        $revokedAt = $this->clock->now()->getTimestamp();

        $this->handler->handle(new Command($tokens->refreshToken));
        $this->clock->advance(10);
        $this->handler->handle(new Command($tokens->refreshToken));

        $stored = $this->refreshTokens->findByHash(new Sha256TokenHasher()->hash($tokens->refreshToken));
        self::assertSame($revokedAt, $stored->revokedAt?->getTimestamp());
    }
}
