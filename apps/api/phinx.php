<?php

declare(strict_types=1);

use Dotenv\Dotenv;

$basePath = __DIR__;
if (file_exists($basePath . '/.env')) {
    Dotenv::createImmutable($basePath)->safeLoad();
}

return [
    'paths' => [
        'migrations' => $basePath . '/database/migrations',
        'seeds' => $basePath . '/database/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => $_ENV['APP_ENV'] ?? 'development',
        'development' => [
            'adapter' => 'pgsql',
            'host' => $_ENV['DB_HOST'] ?? 'db',
            'name' => $_ENV['DB_NAME'] ?? 'tiny_cms',
            'user' => $_ENV['DB_USER'] ?? 'postgres',
            'pass' => $_ENV['DB_PASSWORD'] ?? 'postgres',
            'port' => (int) ($_ENV['DB_PORT'] ?? 5432),
        ],
        'ci' => [
            'adapter' => 'pgsql',
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'name' => $_ENV['DB_NAME'] ?? 'tiny_cms',
            'user' => $_ENV['DB_USER'] ?? 'postgres',
            'pass' => $_ENV['DB_PASSWORD'] ?? 'postgres',
            'port' => (int) ($_ENV['DB_PORT'] ?? 5432),
        ],
    ],
    'version_order' => 'creation',
];
