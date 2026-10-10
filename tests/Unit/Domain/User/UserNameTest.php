<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User;

use App\Domain\User\UserName;
use Codeception\Test\Unit;
use InvalidArgumentException;

final class UserNameTest extends Unit
{
    public function testHoldsValue(): void
    {
        $name = new UserName('John');

        self::assertSame('John', $name->toString());
        self::assertSame('John', (string) $name);
    }

    public function testRejectsTooLongValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UserName(str_repeat('a', UserName::LENGTH_LIMIT + 1));
    }
}
