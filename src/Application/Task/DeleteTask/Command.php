<?php

namespace App\Application\Task\DeleteTask;

use App\Domain\Task\TaskId;

final class Command
{
    public function __construct(
        public TaskId $taskId,
    )
    {}
}
