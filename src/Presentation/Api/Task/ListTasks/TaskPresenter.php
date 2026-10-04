<?php

declare(strict_types=1);

namespace App\Presentation\Api\Task\ListTasks;

use App\Domain\Task\Task;
use App\Presentation\Api\Shared\ResponseFactory\Presenter\PresenterInterface;

/**
 * @implements PresenterInterface<Task>
 */
final readonly class TaskPresenter implements PresenterInterface
{
    public function present(mixed $value): array
    {
        return [
            'id' => $value->id->toString(),
            'title' => $value->title->toString(),
            'description' => $value->description->toString(),
        ];
    }
}
