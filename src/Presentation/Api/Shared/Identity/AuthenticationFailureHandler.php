<?php

declare(strict_types=1);

namespace App\Presentation\Api\Shared\Identity;

use App\Presentation\Api\Shared\ResponseFactory\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Status;

final readonly class AuthenticationFailureHandler implements RequestHandlerInterface
{
    public function __construct(
        private ResponseFactory $responseFactory,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory->fail('Unauthorized.', httpCode: Status::UNAUTHORIZED);
    }
}
