<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200720155510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'pgsql') {
            // this up() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE SEQUENCE contact_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
            $this->addSql('CREATE TABLE contact (id INT NOT NULL, background_id INT DEFAULT NULL, separator_id INT DEFAULT NULL, contact VARCHAR(1024) DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
            $this->addSql('CREATE INDEX IDX_4C62E638C93D69EA ON contact (background_id)');
            $this->addSql('CREATE INDEX IDX_4C62E638A480B5AC ON contact (separator_id)');
            $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638C93D69EA FOREIGN KEY (background_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638A480B5AC FOREIGN KEY (separator_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'pgsql') {
            // this down() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE SCHEMA public');
            $this->addSql('DROP SEQUENCE contact_id_seq CASCADE');
            $this->addSql('DROP TABLE contact');
        }
    }
}
