<?php

declare(strict_types=1);

namespace App\Presentation\Api\Shared\ResponseFactory\Presenter;

use App\Domain\Token\TokenPair;

/**
 * @implements PresenterInterface<TokenPair>
 */
final readonly class TokenPairPresenter implements PresenterInterface
{
    public function present(mixed $value): array
    {
        return [
            'access_token' => $value->accessToken->token,
            'token_type' => 'Bearer',
            'expires_in' => $value->accessToken->expiresIn,
            'refresh_token' => $value->refreshToken,
            'refresh_expires_in' => $value->refreshExpiresIn,
        ];
    }
}
