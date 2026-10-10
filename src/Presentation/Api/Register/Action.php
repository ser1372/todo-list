<?php

namespace App\Presentation\Api\Register;

use App\Application\User\SignUp\Handler;
use App\Presentation\Api\Shared\ResponseFactory\Presenter\TokenPairPresenter;
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
        $tokens = $this->commandHandler->handle(
            $input->createCommand()
        );

        if(null === $tokens) {
            return $this->responseFactory->fail(
                'User already exists',
                httpCode: Status::CONFLICT
            );
        }

        return $this->responseFactory->success(
            $tokens,
            new TokenPairPresenter(),
        );
    }
}
