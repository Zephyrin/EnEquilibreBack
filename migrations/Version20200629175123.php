<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200629175123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'pgsql') {
            // this up() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE SEQUENCE home_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
            $this->addSql('CREATE TABLE home (id INT NOT NULL, background_id INT DEFAULT NULL, separator_id INT DEFAULT NULL, PRIMARY KEY(id))');
            $this->addSql('CREATE INDEX IDX_71D60CD0C93D69EA ON home (background_id)');
            $this->addSql('CREATE INDEX IDX_71D60CD0A480B5AC ON home (separator_id)');
            $this->addSql('ALTER TABLE home ADD CONSTRAINT FK_71D60CD0C93D69EA FOREIGN KEY (background_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->addSql('ALTER TABLE home ADD CONSTRAINT FK_71D60CD0A480B5AC FOREIGN KEY (separator_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'pgsql') {
            // this down() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE SCHEMA public');
            $this->addSql('DROP SEQUENCE home_id_seq CASCADE');
            $this->addSql('DROP TABLE home');
        }
    }
}
