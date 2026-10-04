<?php

namespace App\Presentation\Api\Shared\Identity;

use Yiisoft\Auth\IdentityInterface;
use App\Domain\User\User;

class UserIdentity implements IdentityInterface
{
    public function __construct(
        public User $user,
    ) {}

    public function getId(): ?string
    {
        return $this->user->id->toString();
    }
}
