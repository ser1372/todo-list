<?php

declare(strict_types=1);

namespace App\Infrastructure\Migration;

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;

final class M261004101353CreateTaskTable implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $cb = $b->columnBuilder();

        $b->createTable('tasks', [
            'id' => $cb::char(36)->notNull()->primaryKey(),
            'user_id' => $cb::char(36)->notNull(),
            'title' => $cb::string(50)->notNull(),
            'description' => $cb::string(500)->null(),

            'CONSTRAINT fk_tasks_user
            FOREIGN KEY (user_id)
            REFERENCES users (id)
            ON DELETE RESTRICT
            ON UPDATE RESTRICT',
        ]);

        $b->createIndex('tasks', 'idx_tasks_user_id', 'user_id');
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('tasks');
    }
}
