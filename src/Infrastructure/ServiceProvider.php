<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\User\AuthKeyGeneratorInterface;
use App\Domain\User\PasswordHasherInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Infrastructure\Repository\MysqlTaskRepository;
use App\Infrastructure\Repository\MysqlUserRepository;
use App\Infrastructure\User\RandomAuthKeyGenerator;
use App\Infrastructure\User\YiiPasswordHasher;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\File\FileCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Migration\Service\MigrationService;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;
use Yiisoft\Db\Sqlite\Dsn;
use Yiisoft\Di\ServiceProviderInterface;

use function dirname;

final readonly class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            TaskRepositoryInterface::class => MysqlTaskRepository::class,
            UserRepositoryInterface::class => MysqlUserRepository::class,

            AuthKeyGeneratorInterface::class
            => static fn() => new RandomAuthKeyGenerator(
                length: 64,
                duration: 432000, // 5 days
            ),
            PasswordHasherInterface::class => YiiPasswordHasher::class,
            ConnectionInterface::class => static fn(Aliases $aliases) => new Connection(
                new Driver(
                    new Dsn(databaseName: $aliases->get('@runtime/db.sqlite')),
                ),
                new SchemaCache(
                    new FileCache($aliases->get('@runtime/cache/db')),
                ),
            ),
            MigrationService::class => [
                'setNewMigrationNamespace()' => ['App\\Infrastructure\\Migration'],
                'setSourceNamespaces()' => [['App\\Infrastructure\\Migration']],
                'setSourcePaths()' => [[
                    dirname(__DIR__, 2) . '/vendor/yiisoft/rbac-db/migrations/assignments',
                ]],
            ],
        ];
    }

    public function getExtensions(): array
    {
        return [];
    }
}
