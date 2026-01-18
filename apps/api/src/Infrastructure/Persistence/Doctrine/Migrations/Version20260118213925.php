<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260118213925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_sites (user_id UUID NOT NULL, site_id UUID NOT NULL, PRIMARY KEY (user_id, site_id))');
        $this->addSql('CREATE INDEX IDX_5EC2513BA76ED395 ON user_sites (user_id)');
        $this->addSql('CREATE INDEX IDX_5EC2513BF6BD1646 ON user_sites (site_id)');
        $this->addSql('ALTER TABLE user_sites ADD CONSTRAINT FK_5EC2513BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE user_sites ADD CONSTRAINT FK_5EC2513BF6BD1646 FOREIGN KEY (site_id) REFERENCES sites (id)');
        $this->addSql('ALTER TABLE users ADD reset_token VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD reset_token_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_sites DROP CONSTRAINT FK_5EC2513BA76ED395');
        $this->addSql('ALTER TABLE user_sites DROP CONSTRAINT FK_5EC2513BF6BD1646');
        $this->addSql('DROP TABLE user_sites');
        $this->addSql('ALTER TABLE users DROP reset_token');
        $this->addSql('ALTER TABLE users DROP reset_token_expires_at');
    }
}
