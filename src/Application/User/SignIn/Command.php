<?php

declare(strict_types=1);

namespace App\Application\User\SignIn;

use App\Domain\User\Email;
use App\Domain\User\Password;

final class Command
{
    public function __construct(
        public Email $email,
        public Password $password,
    ) {}
}
