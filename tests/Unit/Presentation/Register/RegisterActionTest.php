<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Register;

use App\Application\User\SignUp\Handler;
use App\Presentation\Api\Register\Action;
use App\Presentation\Api\Register\Input;
use App\Tests\Unit\Fake\ApiResponse;
use App\Tests\Unit\Fake\FrozenClock;
use App\Tests\Unit\Fake\InMemoryRefreshTokenRepository;
use App\Tests\Unit\Fake\InMemoryUserRepository;
use App\Tests\Unit\Fake\PlainPasswordHasher;
use Codeception\Test\Unit;
use Yiisoft\Http\Status;

final class RegisterActionTest extends Unit
{
    private Action $action;

    protected function _before(): void
    {
        $this->action = new Action(
            new Handler(new PlainPasswordHasher(), new InMemoryUserRepository(), new InMemoryRefreshTokenRepository(), new FrozenClock()),
            ApiResponse::factory(),
        );
    }

    public function testReturnsTokensForNewUser(): void
    {
        $response = ($this->action)($this->input());

        $data = ApiResponse::data($response)['data'];
        self::assertSame(Status::OK, $response->getStatusCode());
        self::assertNotEmpty($data['access_token']);
        self::assertNotEmpty($data['refresh_token']);
    }

    public function testReturnsConflictForExistingUser(): void
    {
        ($this->action)($this->input());

        $response = ($this->action)($this->input());

        self::assertSame(Status::CONFLICT, $response->getStatusCode());
    }

    private function input(): Input
    {
        $input = new Input();
        $input->name = 'John';
        $input->login = 'user@example.com';
        $input->password = 'secret123';

        return $input;
    }
}
