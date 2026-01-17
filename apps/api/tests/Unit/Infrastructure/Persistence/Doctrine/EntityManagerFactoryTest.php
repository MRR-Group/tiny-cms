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
    private function getMockSettings(array $overrides = []): array
    {
        return array_merge([
            'displayErrorDetails' => true,
            'db' => [
                'driver' => 'pdo_pgsql',
                'host' => 'db',
                'port' => 5432,
                'dbname' => 'tinycms',
                'user' => 'user',
                'password' => 'pass'
            ]
        ], $overrides);
    }
    public function testCreatesEntityManagerAndRegistersTypes(): void
    {
        $settings = $this->getMockSettings();
        $entityManager = EntityManagerFactory::create($settings);

        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $this->assertTrue(Type::hasType(UserIdType::NAME));
        $this->assertTrue(Type::hasType(EmailType::NAME));
        $this->assertTrue(Type::hasType(RoleType::NAME));
    }

    public function testConfiguresPathsCorrectly(): void
    {
        $settings = $this->getMockSettings();
        $em = EntityManagerFactory::create($settings);
        $config = $em->getConfiguration();
        $driver = $config->getMetadataDriverImpl();

        // Ensure paths are configured correctly
        $paths = $driver->getLocator()->getPaths();
        $this->assertCount(1, $paths);
        $this->assertStringEndsWith("/Infrastructure/Persistence/Doctrine/Mapping", $paths[0]);
    }

    public function testConfiguresConnectionFromEnv(): void
    {
        $settings = $this->getMockSettings([
            'db' => [
                'driver' => 'pdo_pgsql',
                'host' => 'test-db',
                'port' => 5433,
                'dbname' => 'test_cms',
                'user' => 'test_user',
                'password' => 'test_pass',
            ]
        ]);

        $em = EntityManagerFactory::create($settings);
        $params = $em->getConnection()->getParams();

        $this->assertEquals("test-db", $params["host"]);
        $this->assertEquals(5433, $params["port"]);
        $this->assertEquals("test_cms", $params["dbname"]);
        $this->assertEquals("test_user", $params["user"]);
        $this->assertEquals("test_pass", $params["password"]);
    }

    public function testUsesDefaultEnvValues(): void
    {
        // This test used to verify ENV fallback. Since we moved to creating factory with explicit settings,
        // we essentially verify that settings are respected (which is covered by previous test).
        // Or we could test that we can pass minimal settings if we support defaults in factory?
        // But factory references key indices directly, so no defaults in factory.
        // We will just verify it works with default mock settings that simulate "defaults".
        $settings = $this->getMockSettings();
        $em = EntityManagerFactory::create($settings);
        $params = $em->getConnection()->getParams();

        $this->assertEquals("db", $params["host"]);
        $this->assertEquals(5432, $params["port"]);
    }

    public function testConfiguresProductionCache(): void
    {
        $settings = $this->getMockSettings(['displayErrorDetails' => false]);
        $em = EntityManagerFactory::create($settings);
        $config = $em->getConfiguration();

        // Check metadata cache
        $this->assertInstanceOf(FilesystemAdapter::class, $config->getMetadataCache());
        $this->assertInstanceOf(FilesystemAdapter::class, $config->getQueryCache());
        $this->assertInstanceOf(FilesystemAdapter::class, $config->getResultCache());
    }

    public function testConfiguresDevCacheByDefault(): void
    {
        $settings = $this->getMockSettings(['displayErrorDetails' => true]);
        $em = EntityManagerFactory::create($settings);
        $config = $em->getConfiguration();

        // Check metadata cache
        $this->assertInstanceOf(ArrayAdapter::class, $config->getMetadataCache());
        $this->assertInstanceOf(ArrayAdapter::class, $config->getQueryCache());
        $this->assertInstanceOf(ArrayAdapter::class, $config->getResultCache());
    }

    public function testDefaultsToProductionModeWhenSettingMissing(): void
    {
        $settings = $this->getMockSettings();
        // unset displayErrorDetails to trigger default value
        unset($settings['displayErrorDetails']);

        $em = EntityManagerFactory::create($settings);
        $config = $em->getConfiguration();

        // Should default to production (FilesystemAdapter)
        $this->assertInstanceOf(FilesystemAdapter::class, $config->getMetadataCache());
    }
}
