<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Shared\Access;

use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskTitle;
use App\Domain\User\Email;
use App\Domain\User\User;
use App\Domain\User\UserId;
use App\Domain\User\UserName;
use App\Presentation\Api\Shared\Access\CheckTaskOwner;
use App\Presentation\Api\Shared\Identity\AuthenticatedUserProvider;
use App\Presentation\Api\Shared\Identity\IdentityRepository;
use App\Presentation\Api\Shared\Identity\UserIdentity;
use App\Tests\Unit\Fake\ApiResponse;
use App\Tests\Unit\Fake\InMemoryTaskRepository;
use App\Tests\Unit\Fake\InMemoryUserRepository;
use App\Tests\Unit\Fake\NullEventDispatcher;
use Codeception\Test\Unit;
use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\Route;
use Yiisoft\User\CurrentUser;

final class CheckTaskOwnerTest extends Unit
{
    private InMemoryTaskRepository $tasks;
    private User $user;
    private CurrentRoute $currentRoute;
    private CheckTaskOwner $middleware;

    protected function _before(): void
    {
        $this->tasks = new InMemoryTaskRepository();
        $this->user = new User(new UserId(Uuid::uuid7()), new Email('user@example.com'), new UserName('John'), 'hash');
        $currentUser = new CurrentUser(new IdentityRepository(new InMemoryUserRepository()), new NullEventDispatcher());
        $currentUser->overrideIdentity(new UserIdentity($this->user));
        $this->currentRoute = new CurrentRoute();
        $this->middleware = new CheckTaskOwner(
            $this->currentRoute,
            $this->tasks,
            new AuthenticatedUserProvider($currentUser),
            ApiResponse::factory(),
        );
    }

    public function testPassesForOwner(): void
    {
        $task = $this->addTask($this->user->id);

        self::assertSame(Status::OK, $this->process($task->id->toString())->getStatusCode());
    }

    public function testForbidsForeignTask(): void
    {
        $task = $this->addTask(new UserId(Uuid::uuid7()));

        self::assertSame(Status::FORBIDDEN, $this->process($task->id->toString())->getStatusCode());
    }

    public function testNotFoundForMissingTask(): void
    {
        self::assertSame(Status::NOT_FOUND, $this->process(Uuid::uuid7()->toString())->getStatusCode());
    }

    public function testNotFoundForInvalidId(): void
    {
        self::assertSame(Status::NOT_FOUND, $this->process('not-a-uuid')->getStatusCode());
    }

    private function process(string $id): ResponseInterface
    {
        $this->currentRoute->setRouteWithArguments(Route::delete('/tasks/{id}'), ['id' => $id]);

        return $this->middleware->process(new ServerRequest(), new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response(Status::OK);
            }
        });
    }

    private function addTask(UserId $owner): Task
    {
        $task = new Task(new TaskId(Uuid::uuid7()), new TaskTitle('Title'), new TaskDescription('Text'), $owner);
        $this->tasks->createTask($task);

        return $task;
    }
}
