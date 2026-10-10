<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Shared\Identity;

use App\Domain\User\Email;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Presentation\Api\Shared\Identity\AuthenticatedUserProvider;
use App\Presentation\Api\Shared\Identity\IdentityRepository;
use App\Presentation\Api\Shared\Identity\UserIdentity;
use App\Tests\Unit\Fake\InMemoryUserRepository;
use App\Tests\Unit\Fake\NullEventDispatcher;
use Codeception\Test\Unit;
use LogicException;
use Ramsey\Uuid\Uuid;
use Yiisoft\User\CurrentUser;

final class AuthenticatedUserProviderTest extends Unit
{
    private CurrentUser $currentUser;

    protected function _before(): void
    {
        $this->currentUser = new CurrentUser(new IdentityRepository(new InMemoryUserRepository()), new NullEventDispatcher());
    }

    public function testReturnsAuthenticatedUserId(): void
    {
        $user = new User(new UserId(Uuid::uuid7()), new Email('user@example.com'), new UserName('John'), 'hash');
        $this->currentUser->overrideIdentity(new UserIdentity($user));

        self::assertTrue($user->id->isEqualTo(new AuthenticatedUserProvider($this->currentUser)->getId()));
    }

    public function testThrowsForGuest(): void
    {
        $this->expectException(LogicException::class);

        new AuthenticatedUserProvider($this->currentUser)->getId();
    }
}
