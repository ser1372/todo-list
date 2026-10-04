<?php

namespace App\Domain\Task;

use DomainException;

class TaskAccessDenied extends DomainException
{
    public function __construct()
    {
        parent::__construct("Task access denied");
    }
}
