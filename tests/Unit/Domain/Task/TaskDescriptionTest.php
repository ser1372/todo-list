<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\TaskDescription;
use Codeception\Test\Unit;
use InvalidArgumentException;

final class TaskDescriptionTest extends Unit
{
    public function testHoldsValue(): void
    {
        self::assertSame('Text', new TaskDescription('Text')->toString());
        self::assertSame('Text', (string) new TaskDescription('Text'));
    }

    public function testRejectsTooLongValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TaskDescription(str_repeat('a', TaskDescription::LENGTH_LIMIT + 1));
    }
}
