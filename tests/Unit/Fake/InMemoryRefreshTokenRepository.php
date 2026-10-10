<?php

declare(strict_types=1);

namespace App\Tests\Unit\Fake;

use App\Domain\Token\AccessTokenGeneratorInterface;
use App\Domain\Token\RefreshToken;
use App\Domain\Token\RefreshTokenRepositoryInterface;
use App\Domain\Token\TokenHash;
use App\Domain\Token\TokenHasherInterface;
use App\Domain\Token\TokenPair;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Infrastructure\Token\Sha256TokenHasher;
use DateTimeInterface;

final class InMemoryRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /** @var array<string, array> */
    private array $rows = [];

    public function __construct(
        private readonly AccessTokenGeneratorInterface $accessTokenGenerator = new FakeAccessTokenGenerator(),
        private readonly int $refreshTtl = 3600,
        private readonly TokenHasherInterface $tokenHasher = new Sha256TokenHasher(),
    ) {}

    public function create(User $user, DateTimeInterface $now): TokenPair
    {
        $token = RefreshToken::create($user->id, $now, $this->refreshTtl, $this->tokenHasher);
        $this->rows[$token->tokenHash->toString()] = $token->toArray();

        return new TokenPair($this->accessTokenGenerator->generate($user->id), $token->plainToken, $this->refreshTtl);
    }

    public function findByHash(TokenHash $tokenHash): ?RefreshToken
    {
        $row = $this->rows[$tokenHash->toString()] ?? null;

        return null === $row ? null : RefreshToken::fromArray($row);
    }

    public function revoke(RefreshToken $token): bool
    {
        $key = $token->tokenHash->toString();
        if (null !== $this->rows[$key]['revoked_at']) {
            return false;
        }
        $this->rows[$key]['revoked_at'] = $token->revokedAt->getTimestamp();
        return true;
    }

    public function revokeAllForUser(UserId $userId, DateTimeInterface $now): void
    {
        foreach ($this->rows as &$row) {
            if ($userId->toString() === $row['user_id'] && null === $row['revoked_at']) {
                $row['revoked_at'] = $now->getTimestamp();
            }
        }
    }

    public function count(): int
    {
        return count($this->rows);
    }
}
