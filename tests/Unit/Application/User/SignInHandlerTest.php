<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User;

use App\Application\User\SignIn\Command;
use App\Application\User\SignIn\Handler;
use App\Domain\User\Email;
use App\Domain\User\Password;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Tests\Unit\Fake\FrozenClock;
use App\Tests\Unit\Fake\InMemoryRefreshTokenRepository;
use App\Tests\Unit\Fake\InMemoryUserRepository;
use App\Tests\Unit\Fake\PlainPasswordHasher;
use App\Infrastructure\Token\Sha256TokenHasher;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;

final class SignInHandlerTest extends Unit
{
    private InMemoryRefreshTokenRepository $refreshTokens;
    private Handler $handler;
    private User $user;

    protected function _before(): void
    {
        $hasher = new PlainPasswordHasher();
        $users = new InMemoryUserRepository();
        $this->user = new User(
            new UserId(Uuid::uuid7()),
            new Email('user@example.com'),
            new UserName('John'),
            $hasher->hash(new Password('secret123')),
        );
        $users->createUser($this->user);
        $this->refreshTokens = new InMemoryRefreshTokenRepository();
        $this->handler = new Handler($hasher, $users, $this->refreshTokens, new FrozenClock());
    }

    public function testReturnsTokensForValidCredentials(): void
    {
        $tokens = $this->handler->handle(new Command(new Email('user@example.com'), new Password('secret123')));

        self::assertNotNull($tokens);
        self::assertSame('access-' . $this->user->id->toString(), $tokens->accessToken->token);
        self::assertNotNull($this->refreshTokens->findByHash(new Sha256TokenHasher()->hash($tokens->refreshToken)));
    }

    public function testEachSignInCreatesSeparateSession(): void
    {
        $command = new Command(new Email('user@example.com'), new Password('secret123'));

        $first = $this->handler->handle($command);
        $second = $this->handler->handle($command);

        self::assertNotSame($first->refreshToken, $second->refreshToken);
        self::assertSame(2, $this->refreshTokens->count());
    }

    public function testReturnsNullForWrongPassword(): void
    {
        self::assertNull($this->handler->handle(new Command(new Email('user@example.com'), new Password('wrongpass'))));
        self::assertSame(0, $this->refreshTokens->count());
    }

    public function testReturnsNullForUnknownEmail(): void
    {
        self::assertNull($this->handler->handle(new Command(new Email('nobody@example.com'), new Password('secret123'))));
        self::assertSame(0, $this->refreshTokens->count());
    }
}
