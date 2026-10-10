<?php

declare(strict_types=1);

namespace App\Domain\Token;

use Stringable;

final readonly class TokenHash implements Stringable
{
    public function __construct(
        private string $value,
    ) {}

    public function isEqualTo(self $other): bool
    {
        return hash_equals($this->value, $other->value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
