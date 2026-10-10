<?php

declare(strict_types=1);

namespace App\Infrastructure\Migration;

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;

final class M261010120100DropAuthKeyFromUsers implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $b->execute('ALTER TABLE users DROP COLUMN auth_key');
    }

    public function down(MigrationBuilder $b): void
    {
        $b->addColumn('users', 'auth_key', $b->columnBuilder()::string(75)->null());
    }
}
