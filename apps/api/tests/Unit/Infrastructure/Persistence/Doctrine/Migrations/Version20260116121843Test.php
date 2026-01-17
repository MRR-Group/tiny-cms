<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Doctrine\Migrations;

use App\Infrastructure\Persistence\Doctrine\Migrations\Version20260116121843;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class Version20260116121843Test extends TestCase
{
    public function testUp(): void
    {
        $connection = $this->createMock(Connection::class);
        $logger = $this->createMock(LoggerInterface::class);
        $migration = $this->createPartialMock(Version20260116121843::class, ['addSql']);

        $migration->__construct($connection, $logger);

        $schema = $this->createMock(Schema::class);

        $migration->expects($this->exactly(2))
            ->method('addSql')
            ->willReturnCallback(function (string $sql) {
                static $count = 0;
                $count++;
                if ($count === 1) {
                    $this->assertStringContainsString('CREATE TABLE users', $sql);
                } elseif ($count === 2) {
                    $this->assertStringContainsString('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)', $sql);
                }
            });

        $migration->up($schema);
    }

    public function testDown(): void
    {
        $connection = $this->createMock(Connection::class);
        $logger = $this->createMock(LoggerInterface::class);
        $migration = $this->createPartialMock(Version20260116121843::class, ['addSql']);

        $migration->__construct($connection, $logger);

        $schema = $this->createMock(Schema::class);

        $migration->expects($this->once())
            ->method('addSql')
            ->with($this->stringContains('DROP TABLE users'));

        $migration->down($schema);
    }
}
