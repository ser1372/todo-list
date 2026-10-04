<?php

declare(strict_types=1);

namespace App\Presentation\Api\Shared\Identity;

use App\Domain\User\UserId;
use LogicException;
use Yiisoft\User\CurrentUser;

final readonly class AuthenticatedUserProvider
{
    public function __construct(
        private CurrentUser $currentUser,
    ) {}

    public function getId(): UserId
    {
        $data = $this->currentUser->getId();
        if ($data === null) {
            throw new LogicException('User is not authenticated.');
        }

        return UserId::fromString($data);
    }
}
