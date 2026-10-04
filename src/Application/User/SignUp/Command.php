<?php

declare(strict_types=1);

namespace App\Application\User\SignUp;

use App\Domain\User\Email;
use App\Domain\User\Password;
use App\Domain\User\UserName;

final class Command
{
    public function __construct(
        public UserName $name,
        public Email $email,
        public Password $password,
    )
    {}
}
