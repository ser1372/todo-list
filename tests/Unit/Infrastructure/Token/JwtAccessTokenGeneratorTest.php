<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Token;

use App\Domain\User\UserId;
use App\Infrastructure\Token\JwtAccessTokenGenerator;
use App\Tests\Unit\Fake\FrozenClock;
use Codeception\Test\Unit;
use DateTimeImmutable;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Ramsey\Uuid\Uuid;
use Yiisoft\Auth\Jwt\KeyFactory\FromSecret;
use Yiisoft\Auth\Jwt\TokenFactory;
use Yiisoft\Auth\Jwt\TokenRepository;

final class JwtAccessTokenGeneratorTest extends Unit
{
    private AlgorithmManager $algorithms;
    private JWSSerializerManager $serializers;

    protected function _before(): void
    {
        $this->algorithms = new AlgorithmManager([new HS256()]);
        $this->serializers = new JWSSerializerManager([new CompactSerializer()]);
    }

    public function testGeneratedTokenCarriesClaims(): void
    {
        $userId = new UserId(Uuid::uuid7());
        $clock = new FrozenClock(new DateTimeImmutable('@1700000000'));

        $accessToken = $this->generator('a', $clock)->generate($userId);
        $claims = $this->repository('a')->getClaims($accessToken->token);

        self::assertSame(900, $accessToken->expiresIn);
        self::assertSame($userId->toString(), $claims['sub']);
        self::assertSame(1700000000, $claims['iat']);
        self::assertSame(1700000900, $claims['exp']);
        self::assertSame(32, strlen($claims['jti']));
    }

    public function testEachTokenHasUniqueId(): void
    {
        $userId = new UserId(Uuid::uuid7());
        $generator = $this->generator('a', new FrozenClock());
        $repository = $this->repository('a');

        self::assertNotSame(
            $repository->getClaims($generator->generate($userId)->token)['jti'],
            $repository->getClaims($generator->generate($userId)->token)['jti'],
        );
    }

    public function testTokenSignedWithOtherSecretIsRejected(): void
    {
        $token = $this->generator('a', new FrozenClock())->generate(new UserId(Uuid::uuid7()))->token;

        self::assertNull($this->repository('b')->getClaims($token));
    }

    private function generator(string $secretChar, FrozenClock $clock): JwtAccessTokenGenerator
    {
        return new JwtAccessTokenGenerator(
            new TokenFactory(new FromSecret(str_repeat($secretChar, 32)), $this->algorithms, $this->serializers),
            $clock,
            900,
        );
    }

    private function repository(string $secretChar): TokenRepository
    {
        return new TokenRepository(new FromSecret(str_repeat($secretChar, 32)), $this->algorithms, $this->serializers);
    }
}
