<?php

declare(strict_types=1);

namespace App\Presentation\Api\Auth\Refresh;

use App\Application\User\RefreshTokens\Handler;
use App\Domain\Token\InvalidRefreshToken;
use App\Presentation\Api\Shared\ResponseFactory\Presenter\TokenPairPresenter;
use App\Presentation\Api\Shared\ResponseFactory\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Status;

final readonly class Action
{
    public function __construct(
        private Handler $commandHandler,
        private ResponseFactory $responseFactory,
    ) {}

    public function __invoke(Input $input): ResponseInterface
    {
        try {
            $tokens = $this->commandHandler->handle($input->createCommand());
        } catch (InvalidRefreshToken $e) {
            return $this->responseFactory->fail($e->getMessage(), httpCode: Status::UNAUTHORIZED);
        }

        return $this->responseFactory->success($tokens, new TokenPairPresenter());
    }
}
