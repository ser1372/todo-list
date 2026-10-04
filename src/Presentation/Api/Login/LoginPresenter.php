<?php

namespace App\Presentation\Api\Login;

use App\Domain\User\User;
use App\Presentation\Api\Shared\ResponseFactory\Presenter\PresenterInterface;

/**
 * @implements PresenterInterface<User>
 */
final readonly class LoginPresenter implements PresenterInterface
{
    public function present(mixed $value): array
    {
        return [
            'token' => $value->authKey,
        ];
    }
}
