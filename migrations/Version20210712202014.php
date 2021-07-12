<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210712202014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        if ($this->connection->getDatabasePlatform()->getName() === 'sqlite') {
            $this->addSql('CREATE TABLE json_ld (id VARCHAR(150) NOT NULL, json CLOB NOT NULL, PRIMARY KEY(id))');
        } else if ($this->connection->getDatabasePlatform()->getName() === 'mysql') {
            $this->addSql('CREATE TABLE json_ld (id VARCHAR(150) NOT NULL, json TEXT NOT NULL, PRIMARY KEY(id))');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        if (
            $this->connection->getDatabasePlatform()->getName() === 'sqlite'
            || $this->connection->getDatabasePlatform()->getName() === 'mysql'
        )
            $this->addSql('DROP TABLE json_ld');
    }
}
