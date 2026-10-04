<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\User\UserId;
use DateTimeImmutable;

final class Task
{
    public readonly TaskId $id;

    private(set) TaskTitle $title;

    private(set) TaskDescription $description;

    public readonly DateTimeImmutable $createdAt;
    private(set) DateTimeImmutable $updatedAt;

    private(set) UserId $createdBy;

    public function __construct(
        TaskId $id,
        TaskTitle $title,
        TaskDescription $description,
        UserId $createdBy,
    )
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->createdBy = $createdBy;
    }

    public function isCreatedBy(UserId $userId): bool
    {
        return $this->createdBy->isEqualTo($userId);
    }

    public function changeTitle(TaskTitle $title): void
    {
        $this->title = $title;
    }

    public function changeDescription(TaskDescription $description): void
    {
        $this->description = $description;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'user_id' => $this->createdBy,
        ];
    }

    public static function fromArray(array $data): Task
    {
        return new self(
            TaskId::fromString($data['id']),
            new TaskTitle($data['title']),
            new TaskDescription($data['description']),
            UserId::fromString($data['user_id']),
        );
    }
}
