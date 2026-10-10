<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task;

use App\Application\Task\DeleteTask\Command;
use App\Application\Task\DeleteTask\Handler;
use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskNotFound;
use App\Domain\Task\TaskTitle;
use App\Domain\User\UserId;
use App\Tests\Unit\Fake\InMemoryTaskRepository;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;

final class DeleteTaskHandlerTest extends Unit
{
    public function testDeletesTask(): void
    {
        $repository = new InMemoryTaskRepository();
        $task = new Task(new TaskId(Uuid::uuid7()), new TaskTitle('Title'), new TaskDescription('Text'), new UserId(Uuid::uuid7()));
        $repository->createTask($task);

        new Handler($repository)->handle(new Command($task->id));

        self::assertSame([], $repository->all());
    }

    public function testThrowsWhenTaskMissing(): void
    {
        $this->expectException(TaskNotFound::class);

        new Handler(new InMemoryTaskRepository())->handle(new Command(new TaskId(Uuid::uuid7())));
    }
}
