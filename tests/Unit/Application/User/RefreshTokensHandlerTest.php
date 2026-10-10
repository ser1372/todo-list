<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User;

use App\Application\User\RefreshTokens\Command;
use App\Application\User\RefreshTokens\Handler;
use App\Domain\Token\InvalidRefreshToken;
use App\Domain\Token\TokenPair;
use App\Domain\User\Email;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Tests\Unit\Fake\FrozenClock;
use App\Tests\Unit\Fake\InMemoryRefreshTokenRepository;
use App\Tests\Unit\Fake\InMemoryUserRepository;
use App\Infrastructure\Token\Sha256TokenHasher;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;

final class RefreshTokensHandlerTest extends Unit
{
    private InMemoryRefreshTokenRepository $refreshTokens;
    private InMemoryUserRepository $users;
    private FrozenClock $clock;
    private Handler $handler;
    private User $user;

    protected function _before(): void
    {
        $this->clock = new FrozenClock();
        $this->users = new InMemoryUserRepository();
        $this->user = new User(new UserId(Uuid::uuid7()), new Email('user@example.com'), new UserName('John'), 'hash');
        $this->users->createUser($this->user);
        $this->refreshTokens = new InMemoryRefreshTokenRepository();
        $this->handler = new Handler($this->refreshTokens, new Sha256TokenHasher(), $this->users, $this->clock);
    }

    public function testRotatesRefreshToken(): void
    {
        $initial = $this->createTokens();

        $rotated = $this->handler->handle(new Command($initial->refreshToken));

        self::assertNotSame($initial->refreshToken, $rotated->refreshToken);
        self::assertSame('access-' . $this->user->id->toString(), $rotated->accessToken->token);
        $this->assertRejected($initial->refreshToken);
    }

    public function testRotatedTokenCanBeRefreshedAgain(): void
    {
        $rotated = $this->handler->handle(new Command($this->createTokens()->refreshToken));

        $next = $this->handler->handle(new Command($rotated->refreshToken));

        self::assertNotSame($rotated->refreshToken, $next->refreshToken);
    }

    public function testRejectsUnknownToken(): void
    {
        $this->assertRejected('unknown');
    }

    public function testRejectsExpiredToken(): void
    {
        $tokens = $this->createTokens();
        $this->clock->advance(3600);

        $this->assertRejected($tokens->refreshToken);
    }

    public function testAcceptsTokenRightBeforeExpiry(): void
    {
        $tokens = $this->createTokens();
        $this->clock->advance(3599);

        self::assertInstanceOf(TokenPair::class, $this->handler->handle(new Command($tokens->refreshToken)));
    }

    public function testReuseOfRotatedTokenRevokesAllUserSessions(): void
    {
        $first = $this->createTokens();
        $otherSession = $this->createTokens();
        $rotated = $this->handler->handle(new Command($first->refreshToken));

        $this->assertRejected($first->refreshToken);

        $this->assertRejected($rotated->refreshToken);
        $this->assertRejected($otherSession->refreshToken);
    }

    public function testReuseDoesNotRevokeOtherUsersSessions(): void
    {
        $other = new User(new UserId(Uuid::uuid7()), new Email('other@example.com'), new UserName('Jane'), 'hash');
        $this->users->createUser($other);
        $otherTokens = $this->refreshTokens->create($other, $this->clock->now());
        $first = $this->createTokens();
        $this->handler->handle(new Command($first->refreshToken));

        $this->assertRejected($first->refreshToken);

        self::assertInstanceOf(TokenPair::class, $this->handler->handle(new Command($otherTokens->refreshToken)));
    }

    public function testRejectsTokenOfDeletedUser(): void
    {
        $tokens = $this->createTokens();
        $this->users->delete($this->user->id);

        $this->assertRejected($tokens->refreshToken);
    }

    private function createTokens(): TokenPair
    {
        return $this->refreshTokens->create($this->user, $this->clock->now());
    }

    private function assertRejected(string $refreshToken): void
    {
        try {
            $this->handler->handle(new Command($refreshToken));
            self::fail('Expected InvalidRefreshToken.');
        } catch (InvalidRefreshToken) {
            self::assertTrue(true);
        }
    }
}
