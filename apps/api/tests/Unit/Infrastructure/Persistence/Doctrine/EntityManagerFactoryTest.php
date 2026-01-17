<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Doctrine;

use App\Infrastructure\Persistence\Doctrine\EntityManagerFactory;
use App\Infrastructure\Persistence\Doctrine\Type\EmailType;
use App\Infrastructure\Persistence\Doctrine\Type\RoleType;
use App\Infrastructure\Persistence\Doctrine\Type\UserIdType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class EntityManagerFactoryTest extends TestCase
{
    public function testCreatesEntityManagerAndRegistersTypes(): void
    {
        $entityManager = EntityManagerFactory::create();

        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $this->assertTrue(Type::hasType(UserIdType::NAME));
        $this->assertTrue(Type::hasType(EmailType::NAME));
        $this->assertTrue(Type::hasType(RoleType::NAME));
    }

    public function testConfiguresPathsCorrectly(): void
    {
        $em = EntityManagerFactory::create();
        $config = $em->getConfiguration();
        $driver = $config->getMetadataDriverImpl();

        // Ensure paths are configured correctly
        $paths = $driver->getLocator()->getPaths();
        $this->assertCount(1, $paths);
        $this->assertStringEndsWith("/Infrastructure/Persistence/Doctrine/Mapping", $paths[0]);
    }

    public function testConfiguresConnectionFromEnv(): void
    {
        $originalEnv = $_ENV;
        $_ENV["DB_HOST"] = "test-db";
        $_ENV["DB_PORT"] = "5433";
        $_ENV["DB_DATABASE"] = "test_cms";
        $_ENV["DB_USERNAME"] = "test_user";
        $_ENV["DB_PASSWORD"] = "test_pass";

        try {
            $em = EntityManagerFactory::create();
            $params = $em->getConnection()->getParams();

            $this->assertEquals("test-db", $params["host"]);
            $this->assertEquals(5433, $params["port"]);
            $this->assertEquals("test_cms", $params["dbname"]);
            $this->assertEquals("test_user", $params["user"]);
            $this->assertEquals("test_pass", $params["password"]);
        } finally {
            $_ENV = $originalEnv;
        }
    }

    public function testUsesDefaultEnvValues(): void
    {
        $originalEnv = $_ENV;
        unset($_ENV["DB_HOST"], $_ENV["DB_PORT"], $_ENV["DB_DATABASE"], $_ENV["DB_USERNAME"], $_ENV["DB_PASSWORD"]);

        try {
            $em = EntityManagerFactory::create();
            $params = $em->getConnection()->getParams();

            $this->assertEquals("db", $params["host"]);
            $this->assertEquals(5432, $params["port"]);
        } finally {
            $_ENV = $originalEnv;
        }
    }

    public function testConfiguresProductionCache(): void
    {
        $originalEnv = $_ENV;
        $_ENV["APP_ENV"] = "production";

        try {
            $em = EntityManagerFactory::create();
            $config = $em->getConfiguration();

            // Check metadata cache
            $this->assertInstanceOf(FilesystemAdapter::class, $config->getMetadataCache());
            $this->assertInstanceOf(FilesystemAdapter::class, $config->getQueryCache());
            $this->assertInstanceOf(FilesystemAdapter::class, $config->getResultCache());

            // In production, auto-generate proxy classes should be false (implied by isDevMode=false, but checking specific setting if possible)
            // But checking cache adapter is strong enough to kill the Ternary/Coalesce mutants.
        } finally {
            $_ENV = $originalEnv;
        }
    }

    public function testConfiguresDevCacheByDefault(): void
    {
        $originalEnv = $_ENV;

        if (isset($_ENV["APP_ENV"])) {
            unset($_ENV["APP_ENV"]);
        }

        try {
            $em = EntityManagerFactory::create();
            $config = $em->getConfiguration();

            // Check metadata cache
            $this->assertInstanceOf(ArrayAdapter::class, $config->getMetadataCache());
            $this->assertInstanceOf(ArrayAdapter::class, $config->getQueryCache());
            $this->assertInstanceOf(ArrayAdapter::class, $config->getResultCache());
        } finally {
            $_ENV = $originalEnv;
        }
    }
}
