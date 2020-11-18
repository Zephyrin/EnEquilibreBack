<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200629102531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'pgsql') {
            // this up() migration is auto-generated, please modify it to your needs
            $this->addSql('ALTER TABLE media_object ADD created_by INT DEFAULT NULL');
            $this->addSql('ALTER TABLE media_object ADD CONSTRAINT FK_14D43132DE12AB56 FOREIGN KEY (created_by) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->addSql('CREATE INDEX IDX_14D43132DE12AB56 ON media_object (created_by)');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'pgsql') {
            // this down() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE SCHEMA public');
            $this->addSql('ALTER TABLE media_object DROP CONSTRAINT FK_14D43132DE12AB56');
            $this->addSql('DROP INDEX IDX_14D43132DE12AB56');
            $this->addSql('ALTER TABLE media_object DROP created_by');
        }
    }
}
