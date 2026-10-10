<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Domain\Task\TaskId;
use App\Domain\User\UserId;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;

final class UuidValueTest extends Unit
{
    public function testFromStringRoundTrips(): void
    {
        $uuid = Uuid::uuid7()->toString();

        $id = UserId::fromString($uuid);

        self::assertSame($uuid, $id->toString());
        self::assertSame($uuid, (string) $id);
    }

    public function testTryFromStringReturnsNullForInvalidValue(): void
    {
        self::assertNull(UserId::tryFromString('not-a-uuid'));
        self::assertNull(UserId::tryFromString(null));
    }

    public function testTryFromStringParsesValidValue(): void
    {
        $uuid = Uuid::uuid7()->toString();

        self::assertSame($uuid, UserId::tryFromString($uuid)?->toString());
    }

    public function testIsEqualTo(): void
    {
        $uuid = Uuid::uuid7();

        self::assertTrue(new UserId($uuid)->isEqualTo(new UserId($uuid)));
        self::assertFalse(new UserId($uuid)->isEqualTo(new UserId(Uuid::uuid7())));
    }

    public function testFromStringReturnsConcreteType(): void
    {
        self::assertInstanceOf(TaskId::class, TaskId::fromString(Uuid::uuid7()->toString()));
    }
}
