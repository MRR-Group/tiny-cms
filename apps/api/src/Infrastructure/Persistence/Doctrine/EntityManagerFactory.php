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
    /**
     * @param array<string, mixed> $settings
     */
    public static function create(array $settings): EntityManagerInterface
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

        $isDevMode = $settings['displayErrorDetails'] ?? false;
        $config = ORMSetup::createXMLMetadataConfiguration(paths: [__DIR__ . "/Mapping"], isDevMode: $isDevMode);

        $cache = $isDevMode ? new ArrayAdapter() : new FilesystemAdapter();

        $config->setMetadataCache($cache);
        $config->setQueryCache($cache);
        $config->setResultCache($cache);

        $connection = DriverManager::getConnection($settings['db'], $config);

        return new EntityManager($connection, $config);
    }
}
