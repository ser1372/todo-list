<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskTitle;
use App\Domain\User\UserId;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;

final class TaskTest extends Unit
{
    public function testIsCreatedBy(): void
    {
        $owner = new UserId(Uuid::uuid7());
        $task = $this->createTask($owner);

        self::assertTrue($task->isCreatedBy($owner));
        self::assertFalse($task->isCreatedBy(new UserId(Uuid::uuid7())));
    }

    public function testChangesTitleAndDescription(): void
    {
        $task = $this->createTask(new UserId(Uuid::uuid7()));

        $task->changeTitle(new TaskTitle('New title'));
        $task->changeDescription(new TaskDescription('New description'));

        self::assertSame('New title', $task->title->toString());
        self::assertSame('New description', $task->description->toString());
    }

    public function testArrayRoundTrip(): void
    {
        $owner = new UserId(Uuid::uuid7());
        $task = $this->createTask($owner);

        $restored = Task::fromArray(array_map('strval', $task->toArray()));

        self::assertTrue($restored->id->isEqualTo($task->id));
        self::assertSame('Title', $restored->title->toString());
        self::assertSame('Description', $restored->description->toString());
        self::assertTrue($restored->isCreatedBy($owner));
    }

    private function createTask(UserId $owner): Task
    {
        return new Task(
            new TaskId(Uuid::uuid7()),
            new TaskTitle('Title'),
            new TaskDescription('Description'),
            $owner,
        );
    }
}
