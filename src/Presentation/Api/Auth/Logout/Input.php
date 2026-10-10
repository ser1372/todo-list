<?php

declare(strict_types=1);

namespace App\Presentation\Api\Auth\Logout;

use App\Application\User\Logout\Command;
use Yiisoft\Hydrator\Attribute\Parameter\Trim;
use Yiisoft\Input\Http\AbstractInput;
use Yiisoft\Input\Http\Attribute\Data\FromBody;
use Yiisoft\Validator\Rule\Required;

#[FromBody]
final class Input extends AbstractInput
{
    #[Trim]
    #[Required]
    public string $refresh_token = '';

    public function createCommand(): Command
    {
        return new Command($this->refresh_token);
    }
}
