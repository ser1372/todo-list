<?php

namespace App\Presentation\Api\Task\UpdateTask;

use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskTitle;
use Yiisoft\Hydrator\Attribute\Parameter\Trim;
use Yiisoft\Input\Http\AbstractInput;
use Yiisoft\Input\Http\Attribute\Data\FromBody;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\Uuid;
use App\Application\Task\UpdateTask\Command;

#[FromBody]
final class Input extends AbstractInput
{
    #[RouteArgument('id')]
    #[Uuid(message: 'Invalid task ID.')]
    #[Required]
    public string $id = '';

    #[Trim]
    #[Length(max: 50)]
    public string $title = '';

    #[Trim]
    #[Length(max: 500)]
    public string $description = '';

    public function createCommand(): Command
    {
        return new Command(
            TaskId::fromString($this->id),
            new TaskTitle($this->title),
            new TaskDescription($this->description)
        );
    }
}
