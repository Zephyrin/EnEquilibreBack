<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200702151340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'pgsql') {
            // this up() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE TABLE view_translate (key VARCHAR(128) NOT NULL, translate VARCHAR(64535) DEFAULT NULL, PRIMARY KEY(key))');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'pgsql') {
            // this down() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE SCHEMA public');
            $this->addSql('DROP TABLE view_translate');
        }
    }
}
