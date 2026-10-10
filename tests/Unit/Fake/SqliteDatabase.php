<?php

declare(strict_types=1);

namespace App\Tests\Unit\Fake;

use App\Infrastructure\Migration\M261001124254CreateUsersTable;
use App\Infrastructure\Migration\M261004101353CreateTaskTable;
use App\Infrastructure\Migration\M261010120000CreateRefreshTokensTable;
use App\Infrastructure\Migration\M261010120100DropAuthKeyFromUsers;
use Yiisoft\Cache\File\FileCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Migration\Informer\NullMigrationInformer;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;
use Yiisoft\Db\Sqlite\Dsn;

final class SqliteDatabase
{
    public static function create(): ConnectionInterface
    {
        $db = new Connection(
            new Driver(new Dsn(databaseName: ':memory:')),
            new SchemaCache(new FileCache(sys_get_temp_dir() . '/todo-app-test-schema-' . uniqid())),
        );

        $builder = new MigrationBuilder($db, new NullMigrationInformer());
        foreach ([
            new M261001124254CreateUsersTable(),
            new M261004101353CreateTaskTable(),
            new M261010120000CreateRefreshTokensTable(),
            new M261010120100DropAuthKeyFromUsers(),
        ] as $migration) {
            $migration->up($builder);
        }

        return $db;
    }
}
