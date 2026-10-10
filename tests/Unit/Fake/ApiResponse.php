<?php

declare(strict_types=1);

namespace App\Tests\Unit\Fake;

use App\Presentation\Api\Shared\ResponseFactory\ResponseFactory;
use HttpSoft\Message\ResponseFactory as PsrResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\ResponseFactory\DataResponseFactory;

final class ApiResponse
{
    public static function factory(): ResponseFactory
    {
        return new ResponseFactory(new DataResponseFactory(new PsrResponseFactory()));
    }

    public static function data(ResponseInterface $response): mixed
    {
        $body = $response->getBody();

        return $body instanceof DataStream ? $body->getData() : null;
    }
}
