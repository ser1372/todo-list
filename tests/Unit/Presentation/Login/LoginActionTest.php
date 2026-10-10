<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Login;

use App\Application\User\SignIn\Handler;
use App\Domain\User\Email;
use App\Domain\User\Password;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Presentation\Api\Login\Action;
use App\Presentation\Api\Login\Input;
use App\Tests\Unit\Fake\ApiResponse;
use App\Tests\Unit\Fake\FrozenClock;
use App\Tests\Unit\Fake\InMemoryRefreshTokenRepository;
use App\Tests\Unit\Fake\InMemoryUserRepository;
use App\Tests\Unit\Fake\PlainPasswordHasher;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;
use Yiisoft\Http\Status;

final class LoginActionTest extends Unit
{
    private Action $action;

    protected function _before(): void
    {
        $hasher = new PlainPasswordHasher();
        $users = new InMemoryUserRepository();
        $users->createUser(new User(
            new UserId(Uuid::uuid7()),
            new Email('user@example.com'),
            new UserName('John'),
            $hasher->hash(new Password('secret123')),
        ));
        $this->action = new Action(
            new Handler($hasher, $users, new InMemoryRefreshTokenRepository(), new FrozenClock()),
            ApiResponse::factory(),
        );
    }

    public function testReturnsTokensForValidCredentials(): void
    {
        $response = ($this->action)($this->input('user@example.com', 'secret123'));

        $data = ApiResponse::data($response)['data'];
        self::assertSame(Status::OK, $response->getStatusCode());
        self::assertSame('Bearer', $data['token_type']);
        self::assertNotEmpty($data['access_token']);
        self::assertNotEmpty($data['refresh_token']);
    }

    public function testReturnsNotFoundForWrongPassword(): void
    {
        $response = ($this->action)($this->input('user@example.com', 'wrongpass'));

        self::assertSame(Status::NOT_FOUND, $response->getStatusCode());
    }

    private function input(string $login, string $password): Input
    {
        $input = new Input();
        $input->login = $login;
        $input->password = $password;

        return $input;
    }
}
