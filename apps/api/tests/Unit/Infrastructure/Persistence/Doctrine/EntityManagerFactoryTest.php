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
}
