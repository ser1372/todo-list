<?php

declare(strict_types=1);

namespace App\Presentation\Api\Shared\Identity;

use App\Domain\User\UserId;
use App\Domain\User\UserNotFound;
use App\Domain\User\UserRepositoryInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Auth\IdentityWithTokenRepositoryInterface;

final readonly class IdentityRepository implements IdentityRepositoryInterface, IdentityWithTokenRepositoryInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function findIdentity(string $id): ?IdentityInterface
    {
        $userId = UserId::tryFromString($id);
        if ($userId === null) {
            return null;
        }

        try {
            return new UserIdentity($this->userRepository->getUserById($userId));
        } catch (UserNotFound) {}

        return null;
    }

    public function findIdentityByToken(string $token, ?string $type = null): ?IdentityInterface
    {
        try {
            return new UserIdentity($this->userRepository->getUserByAuthKey($token));
        } catch (UserNotFound) {}

        return null;
    }
}
