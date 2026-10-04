<?php

namespace App\Infrastructure\Repository;

use App\Domain\Task\Task;
use App\Domain\Task\TaskAccessDenied;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskNotFound;
use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\User\UserId;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class MysqlTaskRepository implements TaskRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $db,
    ) {}

    public function createTask(Task $task): void
    {
        $this->db->createCommand()
            ->insert('tasks', $task->toArray())
            ->execute();
    }

    public function deleteTask(Task $task): void
    {
        $this->db->createCommand()
            ->delete('tasks', ['id' => $task->id])
            ->execute();
    }

    public function getTask(TaskId $taskId): Task
    {
        $data = $this->db->createQuery()
            ->select(['id', 'title', 'description', 'user_id'])
            ->from('tasks')
            ->where(['id' => $taskId->toString()])
            ->one();

        if (null === $data) {
            throw new TaskNotFound();
        }

        return Task::fromArray($data);
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
        $this->db->createCommand()
            ->update('tasks',
                ['title' => $task->title, 'description' => $task->description],
                ['id' => $task->id]
            )
            ->execute();
    }
}
