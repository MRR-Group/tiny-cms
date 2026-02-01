<?php

declare(strict_types=1);

return [
    'settings' => [
        'displayErrorDetails' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'logErrors' => true,
        'logErrorDetails' => true,
        'db' => [
            'driver' => 'pdo_' . ($_ENV['DB_CONNECTION'] ?? 'pgsql'),
            'host' => $_ENV['DB_HOST'] ?? 'db',
            'port' => $_ENV['DB_PORT'] ?? 5432,
            'dbname' => $_ENV['DB_DATABASE'] ?? 'tinycms',
            'user' => $_ENV['DB_USERNAME'] ?? 'tinycms',
            'password' => $_ENV['DB_PASSWORD'] ?? 'tinycms',
        ],
        'mail' => [
            'dsn' => $_ENV['MAILER_DSN'] ?? 'null://null',
            'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@tinycms.com',
        ],
        'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'default_secret_change_me',
        'frontend_url' => $_ENV['FRONTEND_URL'] ?? 'http://localhost:5173',
    ],
];
