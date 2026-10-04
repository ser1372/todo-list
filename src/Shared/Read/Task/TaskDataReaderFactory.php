<?php

declare(strict_types=1);

namespace App\Shared\Read\Task;

use App\Domain\Task\Task;
use App\Domain\User\UserId;
use Yiisoft\Data\Db\QueryDataReader;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class TaskDataReaderFactory
{
    public function __construct(
        private ConnectionInterface $db,
    ) {}

    public function create(UserId $userId, string $search = '', string $sort = 'desc'): QueryDataReader
    {
        $query = $this->db->createQuery()
            ->select(['id', 'title', 'description', 'user_id'])
            ->from('tasks')
            ->where(['user_id' => $userId->toString()])
            ->orderBy(['id' => $sort === 'asc' ? SORT_ASC : SORT_DESC])
            ->resultCallback(
                static fn(array $rows): array => array_map(Task::fromArray(...), $rows),
            );

        if ($search !== '') {
            $query->andWhere([
                'or',
                ['like', 'title', $search],
                ['like', 'description', $search],
            ]);
        }

        return new QueryDataReader($query);
    }
}
