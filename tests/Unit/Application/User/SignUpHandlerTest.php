<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\User;

use App\Application\User\SignUp\Command;
use App\Application\User\SignUp\Handler;
use App\Domain\User\Email;
use App\Domain\User\Password;
use App\Domain\User\UserName;
use App\Tests\Unit\Fake\FrozenClock;
use App\Tests\Unit\Fake\InMemoryRefreshTokenRepository;
use App\Tests\Unit\Fake\InMemoryUserRepository;
use App\Tests\Unit\Fake\PlainPasswordHasher;
use App\Infrastructure\Token\Sha256TokenHasher;
use Codeception\Test\Unit;

final class SignUpHandlerTest extends Unit
{
    private InMemoryUserRepository $users;
    private InMemoryRefreshTokenRepository $refreshTokens;
    private Handler $handler;

    protected function _before(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->refreshTokens = new InMemoryRefreshTokenRepository();
        $this->handler = new Handler(new PlainPasswordHasher(), $this->users, $this->refreshTokens, new FrozenClock());
    }

    public function testCreatesUserAndReturnsTokens(): void
    {
        $tokens = $this->handler->handle($this->command('user@example.com'));

        $user = $this->users->getUserByEmail(new Email('user@example.com'));
        self::assertNotNull($tokens);
        self::assertSame('John', $user->name->toString());
        self::assertTrue($user->isValidPassword(new Password('secret123'), new PlainPasswordHasher()));
        self::assertSame('access-' . $user->id->toString(), $tokens->accessToken->token);
        self::assertNotNull($this->refreshTokens->findByHash(new Sha256TokenHasher()->hash($tokens->refreshToken)));
    }

    public function testReturnsNullWhenUserExists(): void
    {
        $this->handler->handle($this->command('user@example.com'));

        $tokens = $this->handler->handle($this->command('user@example.com'));

        self::assertNull($tokens);
        self::assertSame(1, $this->users->count());
        self::assertSame(1, $this->refreshTokens->count());
    }

    private function command(string $email): Command
    {
        return new Command(new UserName('John'), new Email($email), new Password('secret123'));
    }
}
