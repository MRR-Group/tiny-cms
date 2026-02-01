<?php

require 'vendor/autoload.php';

use App\Infrastructure\Persistence\Doctrine\EntityManagerFactory;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$settings = require __DIR__ . '/config/settings.php';
$entityManager = EntityManagerFactory::create($settings['settings']);

$config = new PhpFile('migrations.php'); // Or use array config

return DependencyFactory::fromEntityManager($config, new ExistingEntityManager($entityManager));
