<?php

namespace App\Presentation\Api\Shared\Access;

use App\Domain\Task\TaskAccessDenied;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskNotFound;
use App\Domain\Task\TaskRepositoryInterface;
use App\Presentation\Api\Shared\Identity\AuthenticatedUserProvider;
use App\Presentation\Api\Shared\ResponseFactory\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;

final readonly class CheckTaskOwner implements MiddlewareInterface
{
    public function __construct(
        private CurrentRoute $currentRoute,
        private TaskRepositoryInterface $taskRepository,
        private AuthenticatedUserProvider $authenticatedUserProvider,
        private ResponseFactory $responseFactory,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $taskId = TaskId::tryFromString($this->currentRoute->getArgument('id'));
        if (null === $taskId) {
            return $this->responseFactory->notFound('Task not found');
        }

        try {
            $this->taskRepository->getUserTask(
                $this->authenticatedUserProvider->getId(),
                $taskId
            );
        } catch (TaskNotFound $e) {
            return $this->responseFactory->notFound($e->getMessage());
        } catch (TaskAccessDenied) {
            return $this->responseFactory->fail(
                'Access denied',
                httpCode: Status::FORBIDDEN
            );
        }

        return $handler->handle($request);
    }
}
