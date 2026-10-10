<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Shared\Identity;

use App\Domain\User\Email;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Infrastructure\Token\JwtAccessTokenGenerator;
use App\Presentation\Api\Shared\Identity\IdentityRepository;
use App\Presentation\Api\Shared\Identity\JwtAuth;
use App\Tests\Unit\Fake\FrozenClock;
use App\Tests\Unit\Fake\InMemoryUserRepository;
use App\Tests\Unit\Fake\NullEventDispatcher;
use Codeception\Test\Unit;
use DateTimeImmutable;
use HttpSoft\Message\ServerRequest;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Ramsey\Uuid\Uuid;
use Yiisoft\Auth\Jwt\JwtMethod;
use Yiisoft\Auth\Jwt\KeyFactory\FromSecret;
use Yiisoft\Auth\Jwt\TokenFactory;
use Yiisoft\Auth\Jwt\TokenRepository;
use Yiisoft\User\CurrentUser;

final class JwtAuthTest extends Unit
{
    private InMemoryUserRepository $users;
    private CurrentUser $currentUser;
    private FrozenClock $clock;
    private JwtAuth $auth;
    private User $user;

    protected function _before(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->user = new User(new UserId(Uuid::uuid7()), new Email('user@example.com'), new UserName('John'), 'hash');
        $this->users->createUser($this->user);
        $identityRepository = new IdentityRepository($this->users);
        $this->currentUser = new CurrentUser($identityRepository, new NullEventDispatcher());
        $this->clock = new FrozenClock(new DateTimeImmutable());

        $jwtMethod = new JwtMethod(
            $identityRepository,
            new TokenRepository(new FromSecret(str_repeat('a', 32)), ...$this->jose()),
            [new ExpirationTimeChecker(clock: $this->clock)],
        );
        $this->auth = new JwtAuth($jwtMethod, $this->currentUser);
    }

    public function testAuthenticatesValidToken(): void
    {
        $identity = $this->auth->authenticate($this->request($this->token('a')));

        self::assertSame($this->user->id->toString(), $identity?->getId());
        self::assertSame($this->user->id->toString(), $this->currentUser->getId());
    }

    public function testReturnsNullWithoutToken(): void
    {
        self::assertNull($this->auth->authenticate(new ServerRequest()));
        self::assertTrue($this->currentUser->isGuest());
    }

    public function testReturnsNullForExpiredToken(): void
    {
        $token = $this->token('a');
        $this->clock->advance(901);

        self::assertNull($this->auth->authenticate($this->request($token)));
        self::assertTrue($this->currentUser->isGuest());
    }

    public function testReturnsNullForTokenSignedWithOtherSecret(): void
    {
        self::assertNull($this->auth->authenticate($this->request($this->token('b'))));
    }

    public function testReturnsNullForMalformedToken(): void
    {
        self::assertNull($this->auth->authenticate($this->request('garbage')));
    }

    public function testReturnsNullForDeletedUser(): void
    {
        $token = $this->token('a');
        $this->users->delete($this->user->id);

        self::assertNull($this->auth->authenticate($this->request($token)));
    }

    private function token(string $secretChar): string
    {
        return new JwtAccessTokenGenerator(
            new TokenFactory(new FromSecret(str_repeat($secretChar, 32)), ...$this->jose()),
            $this->clock,
            900,
        )->generate($this->user->id)->token;
    }

    private function request(string $token): ServerRequest
    {
        return new ServerRequest()->withHeader('Authorization', 'Bearer ' . $token);
    }

    /**
     * @return array{AlgorithmManager, JWSSerializerManager}
     */
    private function jose(): array
    {
        return [new AlgorithmManager([new HS256()]), new JWSSerializerManager([new CompactSerializer()])];
    }
}
