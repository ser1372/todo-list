<?php

declare(strict_types=1);

use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Serializer\CompactSerializer;

return [
    'yiisoft/aliases' => [
        'aliases' => require __DIR__ . '/aliases.php',
    ],
    'yiisoft/auth-jwt' => [
        'algorithms' => [HS256::class],
        'serializers' => [CompactSerializer::class],
        'key' => [
            'secret' => $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: '',
        ],
    ],
    'app/auth' => [
        'accessTtl' => 900,
        'refreshTtl' => 2592000,
    ],
];
