<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\TaskTitle;
use Codeception\Test\Unit;
use InvalidArgumentException;

final class TaskTitleTest extends Unit
{
    public function testHoldsValue(): void
    {
        self::assertSame('Title', new TaskTitle('Title')->toString());
        self::assertSame('Title', (string) new TaskTitle('Title'));
    }

    public function testRejectsTooLongValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TaskTitle(str_repeat('a', TaskTitle::LENGTH_LIMIT + 1));
    }
}
