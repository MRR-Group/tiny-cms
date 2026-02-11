<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211002000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Add optimistic-lock version column to sites table for existing databases";
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE sites ADD COLUMN IF NOT EXISTS version INT NOT NULL DEFAULT 1");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE sites DROP COLUMN IF EXISTS version");
    }
}
