<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Repository;

use App\Domain\Task\Task;
use App\Domain\Task\TaskAccessDenied;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskNotFound;
use App\Domain\Task\TaskTitle;
use App\Domain\User\Email;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Infrastructure\Repository\MysqlTaskRepository;
use App\Infrastructure\Repository\MysqlUserRepository;
use App\Tests\Unit\Fake\SqliteDatabase;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;

final class MysqlTaskRepositoryTest extends Unit
{
    private MysqlTaskRepository $repository;
    private UserId $owner;

    protected function _before(): void
    {
        $db = SqliteDatabase::create();
        $this->repository = new MysqlTaskRepository($db);
        $this->owner = new UserId(Uuid::uuid7());
        new MysqlUserRepository($db)->createUser(
            new User($this->owner, new Email('user@example.com'), new UserName('John'), 'hash'),
        );
    }

    public function testCreatesAndGetsTask(): void
    {
        $task = $this->task();

        $this->repository->createTask($task);

        $stored = $this->repository->getTask($task->id);
        self::assertSame('Title', $stored->title->toString());
        self::assertSame('Text', $stored->description->toString());
        self::assertTrue($stored->isCreatedBy($this->owner));
    }

    public function testThrowsWhenTaskMissing(): void
    {
        $this->expectException(TaskNotFound::class);

        $this->repository->getTask(new TaskId(Uuid::uuid7()));
    }

    public function testGetUserTaskReturnsOwnedTask(): void
    {
        $task = $this->task();
        $this->repository->createTask($task);

        self::assertTrue($this->repository->getUserTask($this->owner, $task->id)->id->isEqualTo($task->id));
    }

    public function testGetUserTaskDeniesForeignTask(): void
    {
        $task = $this->task();
        $this->repository->createTask($task);

        $this->expectException(TaskAccessDenied::class);

        $this->repository->getUserTask(new UserId(Uuid::uuid7()), $task->id);
    }

    public function testUpdatesTask(): void
    {
        $task = $this->task();
        $this->repository->createTask($task);
        $task->changeTitle(new TaskTitle('New title'));
        $task->changeDescription(new TaskDescription('New text'));

        $this->repository->updateTask($task);

        $stored = $this->repository->getTask($task->id);
        self::assertSame('New title', $stored->title->toString());
        self::assertSame('New text', $stored->description->toString());
    }

    public function testDeletesTask(): void
    {
        $task = $this->task();
        $this->repository->createTask($task);

        $this->repository->deleteTask($task);

        $this->expectException(TaskNotFound::class);
        $this->repository->getTask($task->id);
    }

    private function task(): Task
    {
        return new Task(new TaskId(Uuid::uuid7()), new TaskTitle('Title'), new TaskDescription('Text'), $this->owner);
    }
}
