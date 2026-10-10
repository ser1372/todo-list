<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User;

use App\Domain\User\Password;
use Codeception\Test\Unit;
use InvalidArgumentException;

final class PasswordTest extends Unit
{
    public function testHoldsValue(): void
    {
        $password = new Password('secret123');

        self::assertSame('secret123', $password->toString());
        self::assertSame('secret123', (string) $password);
    }

    public function testAcceptsBoundaryLengths(): void
    {
        self::assertSame(Password::LENGTH_MIN, strlen(new Password(str_repeat('a', Password::LENGTH_MIN))->toString()));
        self::assertSame(Password::LENGTH_MAX, strlen(new Password(str_repeat('a', Password::LENGTH_MAX))->toString()));
    }

    public function testRejectsTooShortValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Password(str_repeat('a', Password::LENGTH_MIN - 1));
    }

    public function testRejectsTooLongValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Password(str_repeat('a', Password::LENGTH_MAX + 1));
    }
}
