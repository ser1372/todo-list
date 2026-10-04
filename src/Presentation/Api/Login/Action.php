<?php

namespace App\Presentation\Api\Login;

use App\Application\User\SignIn\Handler;
use App\Presentation\Api\Shared\ResponseFactory\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Status;

final readonly class Action
{
    public function __construct(
        private Handler $commandHandler,
        private ResponseFactory $responseFactory,
    )
    {}

    public function __invoke(Input $input): ResponseInterface
    {
        $user = $this->commandHandler->handle(
            $input->createCommand()
        );

        if(null === $user) {
            return $this->responseFactory->fail(
                'User not found',
                httpCode: Status::NOT_FOUND
            );
        }

        return $this->responseFactory->success(
            $user,
            new LoginPresenter(),
        );
    }
}
