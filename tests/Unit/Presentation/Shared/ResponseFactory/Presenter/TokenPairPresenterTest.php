<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Shared\ResponseFactory\Presenter;

use App\Domain\Token\AccessToken;
use App\Domain\Token\TokenPair;
use App\Presentation\Api\Shared\ResponseFactory\Presenter\TokenPairPresenter;
use Codeception\Test\Unit;

final class TokenPairPresenterTest extends Unit
{
    public function testPresentsTokenPair(): void
    {
        $result = new TokenPairPresenter()->present(new TokenPair(new AccessToken('jwt', 900), 'refresh', 3600));

        self::assertSame([
            'access_token' => 'jwt',
            'token_type' => 'Bearer',
            'expires_in' => 900,
            'refresh_token' => 'refresh',
            'refresh_expires_in' => 3600,
        ], $result);
    }
}
