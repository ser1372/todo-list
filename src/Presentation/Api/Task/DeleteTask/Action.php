<?php

namespace App\Presentation\Api\Task\DeleteTask;

use App\Application\Task\DeleteTask\Handler;
use App\Presentation\Api\Shared\ResponseFactory\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

final readonly class Action
{
    public function __construct(
        private Handler                   $commandHandler,
        private ResponseFactory           $responseFactory,
    )
    {}

    public function __invoke(Input $input): ResponseInterface
    {
        $this->commandHandler->handle(
            $input->createCommand()
        );

        return $this->responseFactory->success();
    }
}
