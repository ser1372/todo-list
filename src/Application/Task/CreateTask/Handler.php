<?php

declare(strict_types=1);

namespace App\Application\Task\CreateTask;

use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use Ramsey\Uuid\Uuid;

final readonly class Handler
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
    ) {}

    public function handle(Command $command): void
    {
        $task = new Task(
            id: new TaskId(Uuid::uuid7()),
            title: $command->title,
            description: $command->description,
            createdBy: $command->userId,
        );

        $this->taskRepository->createTask($task);
    }
}
