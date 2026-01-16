<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Infrastructure\Persistence\Doctrine\Type\EmailType;
use App\Infrastructure\Persistence\Doctrine\Type\RoleType;
use App\Infrastructure\Persistence\Doctrine\Type\UserIdType;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class EntityManagerFactory
{
    public static function create(): EntityManagerInterface
    {
        if (!Type::hasType(UserIdType::NAME)) {
            Type::addType(UserIdType::NAME, UserIdType::class);
        }

        if (!Type::hasType(EmailType::NAME)) {
            Type::addType(EmailType::NAME, EmailType::class);
        }

        if (!Type::hasType(RoleType::NAME)) {
            Type::addType(RoleType::NAME, RoleType::class);
        }

        $config = ORMSetup::createXMLMetadataConfiguration(
            paths: [__DIR__ . "/Mapping"],
            isDevMode: true,
        );

        $cache = ($_ENV["APP_ENV"] ?? "dev") === "production"
            ? new FilesystemAdapter()
            : new ArrayAdapter();

        $config->setMetadataCache($cache);
        $config->setQueryCache($cache);
        $config->setResultCache($cache);

        $connection = DriverManager::getConnection([
            "driver" => "pdo_pgsql",
            "host" => $_ENV["DB_HOST"] ?? "db",
            "port" => $_ENV["DB_PORT"] ?? 5432,
            "dbname" => $_ENV["DB_DATABASE"] ?? "tiny_cms",
            "user" => $_ENV["DB_USERNAME"] ?? "user",
            "password" => $_ENV["DB_PASSWORD"] ?? "password",
        ], $config);

        return new EntityManager($connection, $config);
    }
}
