<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User;

use App\Domain\User\Email;
use Codeception\Test\Unit;
use InvalidArgumentException;

final class EmailTest extends Unit
{
    public function testHoldsValue(): void
    {
        $email = new Email('user@example.com');

        self::assertSame('user@example.com', $email->toString());
        self::assertSame('user@example.com', (string) $email);
    }

    public function testAcceptsMaxLength(): void
    {
        $value = str_repeat('a', Email::LENGTH_LIMIT);

        self::assertSame($value, new Email($value)->toString());
    }

    public function testRejectsTooLongValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email(str_repeat('a', Email::LENGTH_LIMIT + 1));
    }

    public function testIsEqualTo(): void
    {
        self::assertTrue(new Email('a@example.com')->isEqualTo(new Email('a@example.com')));
        self::assertFalse(new Email('a@example.com')->isEqualTo(new Email('b@example.com')));
    }
}
