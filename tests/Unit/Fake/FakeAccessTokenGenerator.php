<?php

declare(strict_types=1);

namespace App\Tests\Unit\Fake;

use App\Domain\Token\AccessToken;
use App\Domain\Token\AccessTokenGeneratorInterface;
use App\Domain\User\UserId;

final class FakeAccessTokenGenerator implements AccessTokenGeneratorInterface
{
    public function generate(UserId $userId): AccessToken
    {
        return new AccessToken('access-' . $userId->toString(), 900);
    }
}
