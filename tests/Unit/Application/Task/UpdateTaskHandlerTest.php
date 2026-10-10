<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task;

use App\Application\Task\UpdateTask\Command;
use App\Application\Task\UpdateTask\Handler;
use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskNotFound;
use App\Domain\Task\TaskTitle;
use App\Domain\User\UserId;
use App\Tests\Unit\Fake\InMemoryTaskRepository;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;

final class UpdateTaskHandlerTest extends Unit
{
    private InMemoryTaskRepository $repository;
    private Task $task;

    protected function _before(): void
    {
        $this->repository = new InMemoryTaskRepository();
        $this->task = new Task(new TaskId(Uuid::uuid7()), new TaskTitle('Title'), new TaskDescription('Text'), new UserId(Uuid::uuid7()));
        $this->repository->createTask($this->task);
    }

    public function testUpdatesTitleAndDescription(): void
    {
        new Handler($this->repository)->handle(
            new Command($this->task->id, new TaskTitle('New title'), new TaskDescription('New text')),
        );

        $updated = $this->repository->getTask($this->task->id);
        self::assertSame('New title', $updated->title->toString());
        self::assertSame('New text', $updated->description->toString());
    }

    public function testKeepsFieldsPassedAsEmpty(): void
    {
        new Handler($this->repository)->handle(
            new Command($this->task->id, new TaskTitle(''), new TaskDescription('New text')),
        );

        $updated = $this->repository->getTask($this->task->id);
        self::assertSame('Title', $updated->title->toString());
        self::assertSame('New text', $updated->description->toString());
    }

    public function testThrowsWhenTaskMissing(): void
    {
        $this->expectException(TaskNotFound::class);

        new Handler($this->repository)->handle(
            new Command(new TaskId(Uuid::uuid7()), new TaskTitle('T'), new TaskDescription('D')),
        );
    }
}
