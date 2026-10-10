<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Token;

use App\Infrastructure\Token\Sha256TokenHasher;
use Codeception\Test\Unit;

final class Sha256TokenHasherTest extends Unit
{
    public function testHashesWithSha256(): void
    {
        self::assertSame(hash('sha256', 'value'), new Sha256TokenHasher()->hash('value')->toString());
    }

    public function testIsDeterministic(): void
    {
        $hasher = new Sha256TokenHasher();

        self::assertTrue($hasher->hash('value')->isEqualTo($hasher->hash('value')));
        self::assertFalse($hasher->hash('value')->isEqualTo($hasher->hash('other')));
    }
}
