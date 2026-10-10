<?php

declare(strict_types=1);

namespace App\Domain\Token;

use App\Domain\User\User;
use App\Domain\User\UserId;
use DateTimeInterface;

interface RefreshTokenRepositoryInterface
{
    public function create(User $user, DateTimeInterface $now): TokenPair;

    public function findByHash(TokenHash $tokenHash): ?RefreshToken;

    public function revoke(RefreshToken $token): bool;

    public function revokeAllForUser(UserId $userId, DateTimeInterface $now): void;
}
