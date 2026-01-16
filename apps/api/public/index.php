<?php

declare(strict_types=1);

use App\Application;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$app = Application::create();
$app->run();
