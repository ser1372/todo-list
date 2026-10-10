<?php

declare(strict_types=1);

namespace App\Domain\Token;

use DomainException;

final class InvalidRefreshToken extends DomainException
{
    public function __construct()
    {
        parent::__construct('Invalid refresh token');
    }
}
