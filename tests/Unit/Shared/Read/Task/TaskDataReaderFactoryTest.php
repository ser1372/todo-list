<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Read\Task;

use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskTitle;
use App\Domain\User\Email;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Infrastructure\Repository\MysqlTaskRepository;
use App\Infrastructure\Repository\MysqlUserRepository;
use App\Shared\Read\Task\TaskDataReaderFactory;
use App\Tests\Unit\Fake\SqliteDatabase;
use Codeception\Test\Unit;
use Ramsey\Uuid\Uuid;

final class TaskDataReaderFactoryTest extends Unit
{
    private TaskDataReaderFactory $factory;
    private MysqlTaskRepository $tasks;
    private UserId $owner;

    protected function _before(): void
    {
        $db = SqliteDatabase::create();
        $this->factory = new TaskDataReaderFactory($db);
        $this->tasks = new MysqlTaskRepository($db);
        $users = new MysqlUserRepository($db);
        $this->owner = new UserId(Uuid::uuid7());
        $users->createUser(new User($this->owner, new Email('user@example.com'), new UserName('John'), 'hash'));
        $other = new UserId(Uuid::uuid7());
        $users->createUser(new User($other, new Email('other@example.com'), new UserName('Jane'), 'hash'));

        $this->addTask($this->owner, 'Buy milk', 'From the store');
        $this->addTask($this->owner, 'Write code', 'Unit tests');
        $this->addTask($other, 'Foreign', 'Not visible');
    }

    public function testReadsOnlyUserTasksNewestFirst(): void
    {
        self::assertSame(['Write code', 'Buy milk'], $this->titles($this->factory->create($this->owner)->read()));
    }

    public function testSortsAscending(): void
    {
        self::assertSame(['Buy milk', 'Write code'], $this->titles($this->factory->create($this->owner, sort: 'asc')->read()));
    }

    public function testSearchesTitleAndDescription(): void
    {
        self::assertSame(['Buy milk'], $this->titles($this->factory->create($this->owner, 'milk')->read()));
        self::assertSame(['Write code'], $this->titles($this->factory->create($this->owner, 'tests')->read()));
        self::assertSame([], $this->titles($this->factory->create($this->owner, 'Foreign')->read()));
    }

    private function addTask(UserId $owner, string $title, string $description): void
    {
        $this->tasks->createTask(new Task(new TaskId(Uuid::uuid7()), new TaskTitle($title), new TaskDescription($description), $owner));
        usleep(1000);
    }

    /**
     * @param iterable<Task> $tasks
     * @return list<string>
     */
    private function titles(iterable $tasks): array
    {
        $titles = [];
        foreach ($tasks as $task) {
            $titles[] = $task->title->toString();
        }
        return $titles;
    }
}
