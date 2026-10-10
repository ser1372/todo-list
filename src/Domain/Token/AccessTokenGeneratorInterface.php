<?php

declare(strict_types=1);

namespace App\Domain\Token;

use App\Domain\User\UserId;

interface AccessTokenGeneratorInterface
{
    public function generate(UserId $userId): AccessToken;
}
