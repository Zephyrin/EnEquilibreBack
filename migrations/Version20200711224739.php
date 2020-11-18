<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200711224739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'pgsql') {
            // this up() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE SEQUENCE gallery_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
            $this->addSql('CREATE TABLE gallery (id INT NOT NULL, main_id INT DEFAULT NULL, separator_id INT DEFAULT NULL, show_case_id INT DEFAULT NULL, title VARCHAR(1024) DEFAULT NULL, PRIMARY KEY(id))');
            $this->addSql('CREATE INDEX IDX_472B783A627EA78A ON gallery (main_id)');
            $this->addSql('CREATE INDEX IDX_472B783AA480B5AC ON gallery (separator_id)');
            $this->addSql('CREATE INDEX IDX_472B783AA842F916 ON gallery (show_case_id)');
            $this->addSql('CREATE TABLE gallery_medias (gallery_id INT NOT NULL, media_id INT NOT NULL, PRIMARY KEY(gallery_id, media_id))');
            $this->addSql('CREATE INDEX IDX_B05143234E7AF8F ON gallery_medias (gallery_id)');
            $this->addSql('CREATE INDEX IDX_B0514323EA9FDD75 ON gallery_medias (media_id)');
            $this->addSql('ALTER TABLE gallery ADD CONSTRAINT FK_472B783A627EA78A FOREIGN KEY (main_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->addSql('ALTER TABLE gallery ADD CONSTRAINT FK_472B783AA480B5AC FOREIGN KEY (separator_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->addSql('ALTER TABLE gallery ADD CONSTRAINT FK_472B783AA842F916 FOREIGN KEY (show_case_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->addSql('ALTER TABLE gallery_medias ADD CONSTRAINT FK_B05143234E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->addSql('ALTER TABLE gallery_medias ADD CONSTRAINT FK_B0514323EA9FDD75 FOREIGN KEY (media_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'pgsql') {
            // this down() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE SCHEMA public');
            $this->addSql('ALTER TABLE gallery_medias DROP CONSTRAINT FK_B05143234E7AF8F');
            $this->addSql('DROP SEQUENCE gallery_id_seq CASCADE');
            $this->addSql('DROP TABLE gallery');
            $this->addSql('DROP TABLE gallery_medias');
        }
    }
}
