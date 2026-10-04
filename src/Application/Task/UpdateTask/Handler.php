<?php

namespace App\Application\Task\UpdateTask;

use App\Domain\Task\TaskRepositoryInterface;

final readonly class Handler
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
    ) {}

    public function handle(Command $command): void
    {
        $task = $this->taskRepository->getTask(
            $command->taskId
        );

        if ($command->title->toString()) {
            $task->changeTitle($command->title);
        }

        if ($command->description->toString()) {
            $task->changeDescription($command->description);
        }

        $this->taskRepository->updateTask(
            $task
        );
    }
}
