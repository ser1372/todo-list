<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\User\UserId;

interface TaskRepositoryInterface
{
    public function createTask(Task $task): void;

    public function deleteTask(Task $task): void;

    public function getTask(TaskId $taskId): Task;

    /**
     * @param UserId $userId
     * @param TaskId $taskId
     * @throws TaskNotFound
     * @throws TaskAccessDenied
     * @return Task
     */
    public function getUserTask(UserId $userId, TaskId $taskId): Task;

    public function updateTask(Task $task): void;
}
