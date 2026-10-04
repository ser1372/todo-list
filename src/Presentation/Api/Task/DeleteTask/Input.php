<?php

namespace App\Presentation\Api\Task\DeleteTask;

use App\Application\Task\DeleteTask\Command;
use App\Domain\Task\TaskId;
use Yiisoft\Input\Http\AbstractInput;
use Yiisoft\Input\Http\Attribute\Data\FromBody;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\Uuid;

#[FromBody]
final class Input extends AbstractInput
{
    #[RouteArgument('id')]
    #[Uuid(message: 'Invalid task ID.')]
    #[Required]
    public string $id = '';

    public function createCommand(): Command
    {
        return new Command(
            TaskId::fromString($this->id),
        );
    }
}
