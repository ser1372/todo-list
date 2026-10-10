<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use App\Infrastructure\SystemClock;
use Codeception\Test\Unit;

final class SystemClockTest extends Unit
{
    public function testReturnsCurrentTime(): void
    {
        $before = time();
        $now = new SystemClock()->now()->getTimestamp();

        self::assertGreaterThanOrEqual($before, $now);
        self::assertLessThanOrEqual(time(), $now);
    }
}
