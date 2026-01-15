<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use TinyCms\Api\Application;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$app = Application::create();
$app->run();
