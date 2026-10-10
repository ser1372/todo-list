<?php

declare(strict_types=1);

namespace App\Presentation\Api\Auth\Logout;

use App\Application\User\Logout\Handler;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Status;

final readonly class Action
{
    public function __construct(
        private Handler $commandHandler,
        private ResponseFactoryInterface $responseFactory,
    ) {}

    public function __invoke(Input $input): ResponseInterface
    {
        $this->commandHandler->handle($input->createCommand());

        return $this->responseFactory->createResponse(Status::NO_CONTENT);
    }
}
