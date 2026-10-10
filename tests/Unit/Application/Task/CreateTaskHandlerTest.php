<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Task;

use App\Application\Task\CreateTask\Command;
use App\Application\Task\CreateTask\Handler;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskTitle;
use App\Domain\User\UserId;
use App\Tests\Unit\Fake\InMemoryTaskRepository;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;

final class CreateTaskHandlerTest extends Unit
{
    public function testCreatesTask(): void
    {
        $repository = new InMemoryTaskRepository();
        $owner = new UserId(Uuid::uuid7());

        new Handler($repository)->handle(new Command(new TaskTitle('Title'), new TaskDescription('Text'), $owner));

        $tasks = $repository->all();
        self::assertCount(1, $tasks);
        self::assertSame('Title', $tasks[0]->title->toString());
        self::assertSame('Text', $tasks[0]->description->toString());
        self::assertTrue($tasks[0]->isCreatedBy($owner));
    }
}
