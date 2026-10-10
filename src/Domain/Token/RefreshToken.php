<?php

declare(strict_types=1);

namespace App\Domain\Token;

use App\Domain\User\UserId;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use Ramsey\Uuid\Uuid;
use Random\RandomException;

final class RefreshToken
{
    private(set) ?string $plainToken = null;

    public function __construct(
        public readonly RefreshTokenId $id,
        public readonly UserId $userId,
        public readonly TokenHash $tokenHash,
        public readonly DateTimeInterface $expiresAt,
        public readonly DateTimeInterface $createdAt,
        private(set) ?DateTimeInterface $revokedAt = null,
    ) {}

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public static function create(
        UserId $userId,
        DateTimeInterface $now,
        int $ttl,
        TokenHasherInterface $tokenHasher,
    ): self
    {
        $plainToken = bin2hex(random_bytes(32));

        $token = new self(
            id: new RefreshTokenId(Uuid::uuid7()),
            userId: $userId,
            tokenHash: $tokenHasher->hash($plainToken),
            expiresAt: DateTimeImmutable::createFromInterface($now)->modify("+{$ttl} seconds"),
            createdAt: $now,
        );
        $token->plainToken = $plainToken;

        return $token;
    }

    public function isExpired(DateTimeInterface $now): bool
    {
        return $now >= $this->expiresAt;
    }

    public function revoke(DateTimeInterface $now): void
    {
        $this->revokedAt ??= $now;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'user_id' => $this->userId->toString(),
            'token_hash' => $this->tokenHash->toString(),
            'expires_at' => $this->expiresAt->getTimestamp(),
            'created_at' => $this->createdAt->getTimestamp(),
            'revoked_at' => $this->revokedAt?->getTimestamp(),
        ];
    }

    /**
     * @throws DateMalformedStringException
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: RefreshTokenId::fromString($data['id']),
            userId: UserId::fromString($data['user_id']),
            tokenHash: new TokenHash($data['token_hash']),
            expiresAt: new DateTimeImmutable('@' . $data['expires_at']),
            createdAt: new DateTimeImmutable('@' . $data['created_at']),
            revokedAt: null === $data['revoked_at'] ? null : new DateTimeImmutable('@' . $data['revoked_at']),
        );
    }
}
