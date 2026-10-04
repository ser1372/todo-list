<?php

namespace App\Domain\Task;

use DomainException;

class TaskNotFound extends DomainException
{
    public function __construct()
    {
        parent::__construct("Task not found");
    }
}
