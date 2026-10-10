<?php

declare(strict_types=1);

namespace App\Domain\Token;

interface TokenHasherInterface
{
    public function hash(string $plainToken): TokenHash;
}
