<?php

declare(strict_types=1);

namespace App\Infrastructure\Migration;

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

/**
 * Handles the creation of table `users`.
 */
final class M261001124254CreateUsersTable implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $cb = $b->columnBuilder();
        $b->createTable('users', [
            'id' => $cb::char(36)->notNull()->primaryKey(),
            'email' => $cb::string(50)->notNull()->unique(),
            'name' => $cb::string(100)->notNull(),
            'password_hash' => $cb::string()->notNull(),
            'auth_key' => $cb::string(75)->notNull(),
        ]);
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('users');
    }
}
