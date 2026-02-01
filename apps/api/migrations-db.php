<?php

return [
    'dbname' => $_ENV['DB_DATABASE'] ?? 'tiny_cms',
    'user' => $_ENV['DB_USERNAME'] ?? 'user',
    'password' => $_ENV['DB_PASSWORD'] ?? 'password',
    'host' => $_ENV['DB_HOST'] ?? 'db',
    'driver' => 'pdo_pgsql',
];
