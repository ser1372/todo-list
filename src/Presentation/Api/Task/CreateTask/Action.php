<?php

namespace App\Presentation\Api\Task\CreateTask;

use App\Application\Task\CreateTask\Handler;
use App\Presentation\Api\Shared\Identity\AuthenticatedUserProvider;
use App\Presentation\Api\Shared\ResponseFactory\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

final readonly class Action
{
    public function __construct(
        private Handler $commandHandler,
        private ResponseFactory $responseFactory,
        private AuthenticatedUserProvider $authenticatedUserProvider,
    )
    {}

    public function __invoke(Input $input): ResponseInterface
    {
        $this->commandHandler->handle(
            $input->createCommand(
                $this->authenticatedUserProvider->getId(),
            )
        );

        return $this->responseFactory->success();
    }
}
