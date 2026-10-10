<?php

declare(strict_types=1);

namespace App\Domain\Token;

final readonly class TokenPair
{
    public function __construct(
        public AccessToken $accessToken,
        public string $refreshToken,
        public int $refreshExpiresIn,
    ) {}
}
