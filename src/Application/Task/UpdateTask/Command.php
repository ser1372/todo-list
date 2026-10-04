<?php

namespace App\Application\Task\UpdateTask;

use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskTitle;

final class Command
{
    public function __construct(
        public taskId $taskId,
        public TaskTitle $title,
        public TaskDescription $description,
    )
    {}
}
