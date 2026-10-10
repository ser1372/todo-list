<?php

declare(strict_types=1);

namespace App\Domain\Token;

final readonly class AccessToken
{
    public function __construct(
        public string $token,
        public int $expiresIn,
    ) {}
}
