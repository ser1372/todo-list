<?php

namespace App\Presentation\Api\Register;

use App\Domain\User\User;
use App\Presentation\Api\Shared\ResponseFactory\Presenter\PresenterInterface;

/**
 * @implements PresenterInterface<User>
 */
class RegisterPresenter implements PresenterInterface
{
    public function present(mixed $value): array
    {
        return [
            'token' => $value->authKey,
        ];
    }
}
