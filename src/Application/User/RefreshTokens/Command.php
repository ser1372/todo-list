<?php

declare(strict_types=1);

namespace App\Application\User\RefreshTokens;

final readonly class Command
{
    public function __construct(
        public string $refreshToken,
    ) {}
}
