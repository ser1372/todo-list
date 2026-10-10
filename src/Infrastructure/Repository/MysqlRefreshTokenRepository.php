<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Token\AccessTokenGeneratorInterface;
use App\Domain\Token\RefreshToken;
use App\Domain\Token\RefreshTokenRepositoryInterface;
use App\Domain\Token\TokenHash;
use App\Domain\Token\TokenHasherInterface;
use App\Domain\Token\TokenPair;
use App\Domain\User\User;
use App\Domain\User\UserId;
use DateMalformedStringException;
use DateTimeInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class MysqlRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $db,
        private AccessTokenGeneratorInterface $accessTokenGenerator,
        private TokenHasherInterface $tokenHasher,
        private int $refreshTtl,
        private LoggerInterface $logger,
    ) {}

    public function create(User $user, DateTimeInterface $now): TokenPair
    {
        $token = RefreshToken::create($user->id, $now, $this->refreshTtl, $this->tokenHasher);

        $this->db->createCommand()
            ->insert('refresh_tokens', $token->toArray())
            ->execute();

        return new TokenPair(
            $this->accessTokenGenerator->generate($user->id),
            $token->plainToken,
            $this->refreshTtl,
        );
    }

    public function findByHash(TokenHash $tokenHash): ?RefreshToken
    {
        try {
            $data = $this->db->createQuery()
                ->select(['id', 'user_id', 'token_hash', 'expires_at', 'created_at', 'revoked_at'])
                ->from('refresh_tokens')
                ->where(['token_hash' => $tokenHash->toString()])
                ->one();

            return null === $data ? null : RefreshToken::fromArray($data);
        } catch (DateMalformedStringException) {
            $this->logger->warning(
                'Unable to retrieve refresh token from hash: ' . $tokenHash
            );
        }

        return null;
    }

    public function revoke(RefreshToken $token): bool
    {
        $affected = $this->db->createCommand()
            ->update(
                'refresh_tokens',
                ['revoked_at' => $token->revokedAt?->getTimestamp() ?? time()],
                ['id' => $token->id->toString(), 'revoked_at' => null],
            )
            ->execute();

        return $affected > 0;
    }

    public function revokeAllForUser(UserId $userId, DateTimeInterface $now): void
    {
        $this->db->createCommand()
            ->update(
                'refresh_tokens',
                ['revoked_at' => $now->getTimestamp()],
                ['user_id' => $userId->toString(), 'revoked_at' => null],
            )
            ->execute();
    }
}
