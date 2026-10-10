<?php

declare(strict_types=1);

namespace App\Application\User\Logout;

final readonly class Command
{
    public function __construct(
        public string $refreshToken,
    ) {}
}
