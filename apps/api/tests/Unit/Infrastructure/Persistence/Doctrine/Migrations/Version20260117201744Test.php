<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Doctrine\Migrations;

use App\Infrastructure\Persistence\Doctrine\Migrations\Version20260117201744;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class Version20260117201744Test extends TestCase
{
    public function testUp(): void
    {
        $connection = $this->createMock(Connection::class);
        $logger = $this->createMock(LoggerInterface::class);
        $migration = $this->createPartialMock(Version20260117201744::class, ["addSql"]);

        $migration->__construct($connection, $logger);

        $schema = $this->createMock(Schema::class);

        $migration->expects($this->exactly(2))
            ->method("addSql")
            ->willReturnCallback(function (string $sql): void {
                if (str_contains($sql, "ADD reset_token VARCHAR(100)")) {
                    $this->assertTrue(true);
                } elseif (str_contains($sql, "ADD reset_token_expires_at TIMESTAMP(0)")) {
                    $this->assertTrue(true);
                } else {
                    $this->fail("Unexpected SQL: $sql");
                }
            });

        $migration->up($schema);
    }

    public function testDown(): void
    {
        $connection = $this->createMock(Connection::class);
        $logger = $this->createMock(LoggerInterface::class);
        $migration = $this->createPartialMock(Version20260117201744::class, ["addSql"]);

        $migration->__construct($connection, $logger);

        $schema = $this->createMock(Schema::class);

        $migration->expects($this->exactly(2))
            ->method("addSql")
            ->willReturnCallback(function (string $sql): void {
                if (str_contains($sql, "DROP reset_token") && !str_contains($sql, "reset_token_expires_at")) {
                    $this->assertTrue(true);
                } elseif (str_contains($sql, "DROP reset_token_expires_at")) {
                    $this->assertTrue(true);
                } else {
                    $this->fail("Unexpected SQL: $sql");
                }
            });

        $migration->down($schema);
    }
}
