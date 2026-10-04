<?php

declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\File\FileCache;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\Definitions\Reference;
use Yiisoft\Yii\RateLimiter\Counter;
use Yiisoft\Yii\RateLimiter\CounterInterface;
use Yiisoft\Yii\RateLimiter\LimitRequestsMiddleware;
use Yiisoft\Yii\RateLimiter\Policy\LimitCallback;
use Yiisoft\Yii\RateLimiter\Storage\SimpleCacheStorage;
use Yiisoft\Yii\RateLimiter\Storage\StorageInterface;

return [
    StorageInterface::class => static fn(Aliases $aliases) => new SimpleCacheStorage(
        new FileCache($aliases->get('@runtime/cache/rate-limit')),
    ),

    CounterInterface::class => [
        'class' => Counter::class,
        '__construct()' => [
            'storage' => Reference::to(StorageInterface::class),
            'limit' => 100,
            'periodInSeconds' => 60,
        ],
    ],

    LimitRequestsMiddleware::class => [
        '__construct()' => [
            'limitingPolicy' => DynamicReference::to(
                static fn() => new LimitCallback(
                    static fn(ServerRequestInterface $request): string
                        => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ),
            ),
        ],
    ],
];
