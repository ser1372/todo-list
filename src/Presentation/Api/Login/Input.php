<?php

namespace App\Presentation\Api\Login;

use App\Application\User\SignIn\Command;
use App\Domain\User\Email;
use App\Domain\User\Password;
use Yiisoft\Hydrator\Attribute\Parameter\Trim;
use Yiisoft\Input\Http\AbstractInput;
use Yiisoft\Input\Http\Attribute\Data\FromBody;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Required;

#[FromBody]
final class Input extends AbstractInput
{
    public const string ERROR_MESSAGE = 'Incorrect login or password.';

    #[Trim]
    #[Required]
    public string $login = '';

    #[Required]
    #[Callback(method: 'validateLoginAndPassword', skipOnError: true)]
    public string $password = '';

    private function validateLoginAndPassword(): Result
    {
        $result = new Result();
        if (mb_strlen($this->login) > Email::LENGTH_LIMIT
            || mb_strlen($this->password) < Password::LENGTH_MIN
            || mb_strlen($this->password) > Password::LENGTH_MAX
        ) {
            $result->addError(self::ERROR_MESSAGE);
        }
        return $result;
    }

    public function createCommand(): Command
    {
        return new Command(
            new Email($this->login),
            new Password($this->password),
        );
    }
}
