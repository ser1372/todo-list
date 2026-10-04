<?php

namespace App\Application\Task\DeleteTask;

use App\Domain\Task\TaskRepositoryInterface;

final readonly class Handler
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
    ) {}

    public function handle(Command $command): void
    {
        $this->taskRepository->deleteTask(
            $this->taskRepository->getTask(
                $command->taskId
            )
        );
    }
}
