<?php

declare(strict_types=1);

namespace App\Tests\Unit\Fake;

use App\Domain\Task\Task;
use App\Domain\Task\TaskAccessDenied;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskNotFound;
use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\User\UserId;

final class InMemoryTaskRepository implements TaskRepositoryInterface
{
    /** @var array<string, Task> */
    private array $tasks = [];

    public function createTask(Task $task): void
    {
        $this->tasks[$task->id->toString()] = clone $task;
    }

    public function deleteTask(Task $task): void
    {
        unset($this->tasks[$task->id->toString()]);
    }

    public function getTask(TaskId $taskId): Task
    {
        if (!isset($this->tasks[$taskId->toString()])) {
            throw new TaskNotFound();
        }
        return clone $this->tasks[$taskId->toString()];
    }

    public function getUserTask(UserId $userId, TaskId $taskId): Task
    {
        $task = $this->getTask($taskId);
        if (!$task->isCreatedBy($userId)) {
            throw new TaskAccessDenied();
        }
        return $task;
    }

    public function updateTask(Task $task): void
    {
        $this->tasks[$task->id->toString()] = clone $task;
    }

    /** @return list<Task> */
    public function all(): array
    {
        return array_values($this->tasks);
    }
}
