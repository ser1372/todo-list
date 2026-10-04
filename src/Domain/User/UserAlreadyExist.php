<?php

namespace App\Domain\User;

use DomainException;

final class UserAlreadyExist extends DomainException
{
    public function __construct()
    {
        parent::__construct("User already exist");
    }
}
