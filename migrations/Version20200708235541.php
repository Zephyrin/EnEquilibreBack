<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200708235541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'pgsql') {
            // this up() migration is auto-generated, please modify it to your needs
            $this->addSql('ALTER TABLE home ADD title VARCHAR(1024) DEFAULT NULL');
            $this->addSql('ALTER TABLE home ADD subtitle VARCHAR(1024) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'pgsql') {
            // this down() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE SCHEMA public');
            $this->addSql('ALTER TABLE home DROP title');
            $this->addSql('ALTER TABLE home DROP subtitle');
        }
    }
}
