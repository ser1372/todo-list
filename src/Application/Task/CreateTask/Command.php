<?php

declare(strict_types=1);

namespace App\Application\Task\CreateTask;

use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskTitle;
use App\Domain\User\UserId;

final class Command
{
    public function __construct(
        public TaskTitle $title,
        public TaskDescription $description,
        public UserId $userId,
    )
    {}
}
