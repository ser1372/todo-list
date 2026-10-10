<?php

declare(strict_types=1);

namespace App\Infrastructure\Migration;

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

final class M261010120000CreateRefreshTokensTable implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $cb = $b->columnBuilder();

        $b->createTable('refresh_tokens', [
            'id' => $cb::char(36)->notNull()->primaryKey(),
            'user_id' => $cb::char(36)->notNull(),
            'token_hash' => $cb::char(64)->notNull()->unique(),
            'expires_at' => $cb::integer()->notNull(),
            'created_at' => $cb::integer()->notNull(),
            'revoked_at' => $cb::integer()->null(),

            'CONSTRAINT fk_refresh_tokens_user
            FOREIGN KEY (user_id)
            REFERENCES users (id)
            ON DELETE CASCADE
            ON UPDATE RESTRICT',
        ]);

        $b->createIndex('refresh_tokens', 'idx_refresh_tokens_user_id', 'user_id');
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('refresh_tokens');
    }
}
