<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Auth;

use App\Application\User\RefreshTokens\Handler;
use App\Domain\User\Email;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Presentation\Api\Auth\Refresh\Action;
use App\Presentation\Api\Auth\Refresh\Input;
use App\Tests\Unit\Fake\ApiResponse;
use App\Tests\Unit\Fake\FrozenClock;
use App\Tests\Unit\Fake\InMemoryRefreshTokenRepository;
use App\Tests\Unit\Fake\InMemoryUserRepository;
use App\Infrastructure\Token\Sha256TokenHasher;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;
use Yiisoft\Http\Status;

final class RefreshActionTest extends Unit
{
    private InMemoryRefreshTokenRepository $refreshTokens;
    private FrozenClock $clock;
    private Action $action;
    private User $user;

    protected function _before(): void
    {
        $users = new InMemoryUserRepository();
        $this->user = new User(new UserId(Uuid::uuid7()), new Email('user@example.com'), new UserName('John'), 'hash');
        $users->createUser($this->user);
        $this->refreshTokens = new InMemoryRefreshTokenRepository();
        $this->clock = new FrozenClock();
        $this->action = new Action(new Handler($this->refreshTokens, new Sha256TokenHasher(), $users, $this->clock), ApiResponse::factory());
    }

    public function testReturnsNewTokens(): void
    {
        $tokens = $this->refreshTokens->create($this->user, $this->clock->now());

        $response = ($this->action)($this->input($tokens->refreshToken));

        $data = ApiResponse::data($response);
        self::assertSame(Status::OK, $response->getStatusCode());
        self::assertSame('success', $data['status']);
        self::assertSame('access-' . $this->user->id->toString(), $data['data']['access_token']);
        self::assertNotSame($tokens->refreshToken, $data['data']['refresh_token']);
    }

    public function testReturnsUnauthorizedForInvalidToken(): void
    {
        $response = ($this->action)($this->input('unknown'));

        self::assertSame(Status::UNAUTHORIZED, $response->getStatusCode());
        self::assertSame('Invalid refresh token', ApiResponse::data($response)['error_message']);
    }

    private function input(string $refreshToken): Input
    {
        $input = new Input();
        $input->refresh_token = $refreshToken;

        return $input;
    }
}
