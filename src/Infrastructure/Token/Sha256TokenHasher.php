<?php

declare(strict_types=1);

namespace App\Infrastructure\Token;

use App\Domain\Token\TokenHash;
use App\Domain\Token\TokenHasherInterface;

final readonly class Sha256TokenHasher implements TokenHasherInterface
{
    public function hash(string $plainToken): TokenHash
    {
        return new TokenHash(hash('sha256', $plainToken));
    }
}
