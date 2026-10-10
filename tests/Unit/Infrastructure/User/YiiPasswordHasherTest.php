<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\User;

use App\Domain\User\Password;
use App\Infrastructure\User\YiiPasswordHasher;
use Codeception\Test\Unit;
use Yiisoft\Security\PasswordHasher;

final class YiiPasswordHasherTest extends Unit
{
    public function testHashesAndValidates(): void
    {
        $hasher = new YiiPasswordHasher(new PasswordHasher(PASSWORD_BCRYPT, ['cost' => 4]));

        $hash = $hasher->hash(new Password('secret123'));

        self::assertNotSame('secret123', $hash);
        self::assertTrue($hasher->validate(new Password('secret123'), $hash));
        self::assertFalse($hasher->validate(new Password('wrongpass'), $hash));
    }
}
