<?php

declare(strict_types=1);

namespace App\Domain\User;

use SensitiveParameter;
use Stringable;
use Webmozart\Assert\Assert;

final readonly class Password implements Stringable
{
    public const int LENGTH_MIN = 8;
    public const int LENGTH_MAX = 96;

    /**
     * @param non-empty-string $value
     */
    public function __construct(
        #[SensitiveParameter]
        private string $value,
    ) {
        Assert::lengthBetween($value, self::LENGTH_MIN, self::LENGTH_MAX);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->value;
    }
}
