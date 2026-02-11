<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211001000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Add sections JSON and optimistic-lock version columns to sites table";
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE sites ADD sections JSON NOT NULL DEFAULT '[]'");
        $this->addSql("ALTER TABLE sites ADD version INT NOT NULL DEFAULT 1");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE sites DROP version");
        $this->addSql("ALTER TABLE sites DROP sections");
    }
}
