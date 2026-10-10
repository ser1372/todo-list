<?php

namespace App\Presentation\Api\Task\CreateTask;

use App\Application\Task\CreateTask\Command;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskTitle;
use App\Domain\User\UserId;
use Yiisoft\Hydrator\Attribute\Parameter\Trim;
use Yiisoft\Input\Http\AbstractInput;
use Yiisoft\Input\Http\Attribute\Data\FromBody;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

#[FromBody]
final class Input extends AbstractInput
{
    #[Trim]
    #[Required]
    #[Length(max: 50)]
    public string $title = '';

    #[Trim]
    #[Length(max: 500)]
    public string $description = '';

    public function createCommand(UserId $userId): Command
    {
        return new Command(
            new TaskTitle($this->title),
            new TaskDescription($this->description),
            $userId,
        );
    }
}
