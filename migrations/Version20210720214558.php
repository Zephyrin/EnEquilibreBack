<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210720214558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        if ($this->connection->getDatabasePlatform()->getName() === 'sqlite') {
            $this->addSql(
                'CREATE TABLE event (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                image_id INTEGER DEFAULT NULL,
                title VARCHAR(2048) DEFAULT NULL,
                sub_title VARCHAR(2048) DEFAULT NULL,
                description CLOB DEFAULT NULL,
                order_ INTEGER NOT NULL)'
            );
            $this->addSql('CREATE INDEX IDX_3BAE0AA73DA5256D ON event (image_id)');
        } else if ($this->connection->getDatabasePlatform()->getName() === 'mysql') {
            $this->addSql(
                'CREATE TABLE event (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                image_id INTEGER DEFAULT NULL,
                title VARCHAR(2048) DEFAULT NULL,
                sub_title VARCHAR(2048) DEFAULT NULL,
                description TEXT DEFAULT NULL,
                order_ INTEGER NOT NULL)'
            );
            $this->addSql('CREATE INDEX IDX_3BAE0AA73DA5256D ON event (image_id)');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        if (
            $this->connection->getDatabasePlatform()->getName() === 'sqlite'
            || $this->connection->getDatabasePlatform()->getName() === 'mysql'
        ) {
            $this->addSql('DROP TABLE event');
            $this->addSql('DROP INDEX IDX_3BAE0AA73DA5256D');
        }
    }
}
