<?php

require 'vendor/autoload.php';

use App\Infrastructure\Persistence\Doctrine\EntityManagerFactory;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$entityManager = EntityManagerFactory::create();

$config = new PhpFile('migrations.php'); // Or use array config

return DependencyFactory::fromEntityManager($config, new ExistingEntityManager($entityManager));
