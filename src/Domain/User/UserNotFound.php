<?php

declare(strict_types=1);

namespace App\Domain\User;

use DomainException;

final class UserNotFound extends DomainException
{
    public function __construct()
    {
        parent::__construct("User not found");
    }
}
