<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Shared\Identity;

use App\Presentation\Api\Shared\Identity\AuthenticationFailureHandler;
use App\Tests\Unit\Fake\ApiResponse;
use Codeception\Test\Unit;
use HttpSoft\Message\ServerRequest;
use Yiisoft\Http\Status;

final class AuthenticationFailureHandlerTest extends Unit
{
    public function testReturnsJsonEnvelopeWithUnauthorizedStatus(): void
    {
        $response = new AuthenticationFailureHandler(ApiResponse::factory())->handle(new ServerRequest());

        self::assertSame(Status::UNAUTHORIZED, $response->getStatusCode());
        self::assertSame(['status' => 'failed', 'error_message' => 'Unauthorized.'], ApiResponse::data($response));
    }
}
