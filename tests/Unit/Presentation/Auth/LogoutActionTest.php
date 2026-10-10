<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Auth;

use App\Application\User\Logout\Handler;
use App\Domain\User\Email;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Presentation\Api\Auth\Logout\Action;
use App\Presentation\Api\Auth\Logout\Input;
use App\Tests\Unit\Fake\FrozenClock;
use App\Tests\Unit\Fake\InMemoryRefreshTokenRepository;
use App\Infrastructure\Token\Sha256TokenHasher;
use Codeception\Test\Unit;
use HttpSoft\Message\ResponseFactory;
use Ramsey\Uuid\Uuid;
use Yiisoft\Http\Status;

final class LogoutActionTest extends Unit
{
    public function testRevokesTokenAndReturnsNoContent(): void
    {
        $clock = new FrozenClock();
        $refreshTokens = new InMemoryRefreshTokenRepository();
        $user = new User(new UserId(Uuid::uuid7()), new Email('user@example.com'), new UserName('John'), 'hash');
        $tokens = $refreshTokens->create($user, $clock->now());
        $input = new Input();
        $input->refresh_token = $tokens->refreshToken;

        $response = new Action(new Handler($refreshTokens, new Sha256TokenHasher(), $clock), new ResponseFactory())($input);

        self::assertSame(Status::NO_CONTENT, $response->getStatusCode());
        self::assertNotNull($refreshTokens->findByHash(new Sha256TokenHasher()->hash($tokens->refreshToken))->revokedAt);
    }

    public function testReturnsNoContentForUnknownToken(): void
    {
        $input = new Input();
        $input->refresh_token = 'unknown';

        $response = new Action(
            new Handler(new InMemoryRefreshTokenRepository(), new Sha256TokenHasher(), new FrozenClock()),
            new ResponseFactory(),
        )($input);

        self::assertSame(Status::NO_CONTENT, $response->getStatusCode());
    }
}
