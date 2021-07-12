<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201204183035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'pgsql') {

            // this up() migration is auto-generated, please modify it to your needs
            $this->addSql('DROP INDEX IDX_B5F422E3A480B5AC');
            $this->addSql('DROP INDEX IDX_B5F422E3C93D69EA');
            $this->addSql('CREATE TEMPORARY TABLE __temp__about AS SELECT id, background_id, separator_id, about, comment FROM about');
            $this->addSql('DROP TABLE about');
            $this->addSql('CREATE TABLE about (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, background_id INTEGER DEFAULT NULL, separator_id INTEGER DEFAULT NULL, about VARCHAR(1024) DEFAULT NULL COLLATE BINARY, comment VARCHAR(255) DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_B5F422E3C93D69EA FOREIGN KEY (background_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B5F422E3A480B5AC FOREIGN KEY (separator_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO about (id, background_id, separator_id, about, comment) SELECT id, background_id, separator_id, about, comment FROM __temp__about');
            $this->addSql('DROP TABLE __temp__about');
            $this->addSql('CREATE INDEX IDX_B5F422E3A480B5AC ON about (separator_id)');
            $this->addSql('CREATE INDEX IDX_B5F422E3C93D69EA ON about (background_id)');
            $this->addSql('DROP INDEX IDX_4C62E638A480B5AC');
            $this->addSql('DROP INDEX IDX_4C62E638C93D69EA');
            $this->addSql('CREATE TEMPORARY TABLE __temp__contact AS SELECT id, background_id, separator_id, contact, comment, email FROM contact');
            $this->addSql('DROP TABLE contact');
            $this->addSql('CREATE TABLE contact (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, background_id INTEGER DEFAULT NULL, separator_id INTEGER DEFAULT NULL, contact VARCHAR(1024) DEFAULT NULL COLLATE BINARY, comment VARCHAR(255) DEFAULT NULL COLLATE BINARY, email VARCHAR(255) DEFAULT NULL COLLATE BINARY, phone VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_4C62E638C93D69EA FOREIGN KEY (background_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4C62E638A480B5AC FOREIGN KEY (separator_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO contact (id, background_id, separator_id, contact, comment, email) SELECT id, background_id, separator_id, contact, comment, email FROM __temp__contact');
            $this->addSql('DROP TABLE __temp__contact');
            $this->addSql('CREATE INDEX IDX_4C62E638A480B5AC ON contact (separator_id)');
            $this->addSql('CREATE INDEX IDX_4C62E638C93D69EA ON contact (background_id)');
            $this->addSql('DROP INDEX IDX_472B783AA842F916');
            $this->addSql('DROP INDEX IDX_472B783AA480B5AC');
            $this->addSql('DROP INDEX IDX_472B783A627EA78A');
            $this->addSql('CREATE TEMPORARY TABLE __temp__gallery AS SELECT id, main_id, separator_id, show_case_id, title, order_ FROM gallery');
            $this->addSql('DROP TABLE gallery');
            $this->addSql('CREATE TABLE gallery (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, main_id INTEGER DEFAULT NULL, separator_id INTEGER DEFAULT NULL, show_case_id INTEGER DEFAULT NULL, title VARCHAR(1024) DEFAULT NULL COLLATE BINARY, order_ INTEGER NOT NULL, CONSTRAINT FK_472B783A627EA78A FOREIGN KEY (main_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_472B783AA480B5AC FOREIGN KEY (separator_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_472B783AA842F916 FOREIGN KEY (show_case_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO gallery (id, main_id, separator_id, show_case_id, title, order_) SELECT id, main_id, separator_id, show_case_id, title, order_ FROM __temp__gallery');
            $this->addSql('DROP TABLE __temp__gallery');
            $this->addSql('CREATE INDEX IDX_472B783AA842F916 ON gallery (show_case_id)');
            $this->addSql('CREATE INDEX IDX_472B783AA480B5AC ON gallery (separator_id)');
            $this->addSql('CREATE INDEX IDX_472B783A627EA78A ON gallery (main_id)');
            $this->addSql('DROP INDEX IDX_B0514323EA9FDD75');
            $this->addSql('DROP INDEX IDX_B05143234E7AF8F');
            $this->addSql('CREATE TEMPORARY TABLE __temp__gallery_medias AS SELECT gallery_id, media_id FROM gallery_medias');
            $this->addSql('DROP TABLE gallery_medias');
            $this->addSql('CREATE TABLE gallery_medias (gallery_id INTEGER NOT NULL, media_id INTEGER NOT NULL, PRIMARY KEY(gallery_id, media_id), CONSTRAINT FK_B05143234E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B0514323EA9FDD75 FOREIGN KEY (media_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO gallery_medias (gallery_id, media_id) SELECT gallery_id, media_id FROM __temp__gallery_medias');
            $this->addSql('DROP TABLE __temp__gallery_medias');
            $this->addSql('CREATE INDEX IDX_B0514323EA9FDD75 ON gallery_medias (media_id)');
            $this->addSql('CREATE INDEX IDX_B05143234E7AF8F ON gallery_medias (gallery_id)');
            $this->addSql('DROP INDEX IDX_71D60CD0A480B5AC');
            $this->addSql('DROP INDEX IDX_71D60CD0C93D69EA');
            $this->addSql('CREATE TEMPORARY TABLE __temp__home AS SELECT id, background_id, separator_id, title, subtitle FROM home');
            $this->addSql('DROP TABLE home');
            $this->addSql('CREATE TABLE home (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, background_id INTEGER DEFAULT NULL, separator_id INTEGER DEFAULT NULL, title VARCHAR(1024) DEFAULT NULL COLLATE BINARY, subtitle VARCHAR(1024) DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_71D60CD0C93D69EA FOREIGN KEY (background_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_71D60CD0A480B5AC FOREIGN KEY (separator_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO home (id, background_id, separator_id, title, subtitle) SELECT id, background_id, separator_id, title, subtitle FROM __temp__home');
            $this->addSql('DROP TABLE __temp__home');
            $this->addSql('CREATE INDEX IDX_71D60CD0A480B5AC ON home (separator_id)');
            $this->addSql('CREATE INDEX IDX_71D60CD0C93D69EA ON home (background_id)');
            $this->addSql('DROP INDEX IDX_14D43132DE12AB56');
            $this->addSql('CREATE TEMPORARY TABLE __temp__media_object AS SELECT id, created_by, file_path, description FROM media_object');
            $this->addSql('DROP TABLE media_object');
            $this->addSql('CREATE TABLE media_object (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by INTEGER DEFAULT NULL, file_path VARCHAR(255) NOT NULL COLLATE BINARY, description VARCHAR(1024) DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_14D43132DE12AB56 FOREIGN KEY (created_by) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO media_object (id, created_by, file_path, description) SELECT id, created_by, file_path, description FROM __temp__media_object');
            $this->addSql('DROP TABLE __temp__media_object');
            $this->addSql('CREATE INDEX IDX_14D43132DE12AB56 ON media_object (created_by)');
            $this->addSql('CREATE TEMPORARY TABLE __temp__view_translate AS SELECT "key", translate FROM view_translate');
            $this->addSql('DROP TABLE view_translate');
            $this->addSql('CREATE TABLE view_translate ("key" VARCHAR(128) NOT NULL COLLATE BINARY, translate CLOB DEFAULT NULL, PRIMARY KEY("key"))');
            $this->addSql('INSERT INTO view_translate ("key", translate) SELECT "key", translate FROM __temp__view_translate');
            $this->addSql('DROP TABLE __temp__view_translate');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'pgsql') {

            // this down() migration is auto-generated, please modify it to your needs
            $this->addSql('DROP INDEX IDX_B5F422E3C93D69EA');
            $this->addSql('DROP INDEX IDX_B5F422E3A480B5AC');
            $this->addSql('CREATE TEMPORARY TABLE __temp__about AS SELECT id, background_id, separator_id, about, comment FROM about');
            $this->addSql('DROP TABLE about');
            $this->addSql('CREATE TABLE about (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, background_id INTEGER DEFAULT NULL, separator_id INTEGER DEFAULT NULL, about VARCHAR(1024) DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL)');
            $this->addSql('INSERT INTO about (id, background_id, separator_id, about, comment) SELECT id, background_id, separator_id, about, comment FROM __temp__about');
            $this->addSql('DROP TABLE __temp__about');
            $this->addSql('CREATE INDEX IDX_B5F422E3C93D69EA ON about (background_id)');
            $this->addSql('CREATE INDEX IDX_B5F422E3A480B5AC ON about (separator_id)');
            $this->addSql('DROP INDEX IDX_4C62E638C93D69EA');
            $this->addSql('DROP INDEX IDX_4C62E638A480B5AC');
            $this->addSql('CREATE TEMPORARY TABLE __temp__contact AS SELECT id, background_id, separator_id, contact, comment, email FROM contact');
            $this->addSql('DROP TABLE contact');
            $this->addSql('CREATE TABLE contact (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, background_id INTEGER DEFAULT NULL, separator_id INTEGER DEFAULT NULL, contact VARCHAR(1024) DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL)');
            $this->addSql('INSERT INTO contact (id, background_id, separator_id, contact, comment, email) SELECT id, background_id, separator_id, contact, comment, email FROM __temp__contact');
            $this->addSql('DROP TABLE __temp__contact');
            $this->addSql('CREATE INDEX IDX_4C62E638C93D69EA ON contact (background_id)');
            $this->addSql('CREATE INDEX IDX_4C62E638A480B5AC ON contact (separator_id)');
            $this->addSql('DROP INDEX IDX_472B783A627EA78A');
            $this->addSql('DROP INDEX IDX_472B783AA480B5AC');
            $this->addSql('DROP INDEX IDX_472B783AA842F916');
            $this->addSql('CREATE TEMPORARY TABLE __temp__gallery AS SELECT id, main_id, separator_id, show_case_id, title, order_ FROM gallery');
            $this->addSql('DROP TABLE gallery');
            $this->addSql('CREATE TABLE gallery (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, main_id INTEGER DEFAULT NULL, separator_id INTEGER DEFAULT NULL, show_case_id INTEGER DEFAULT NULL, title VARCHAR(1024) DEFAULT NULL, order_ INTEGER NOT NULL)');
            $this->addSql('INSERT INTO gallery (id, main_id, separator_id, show_case_id, title, order_) SELECT id, main_id, separator_id, show_case_id, title, order_ FROM __temp__gallery');
            $this->addSql('DROP TABLE __temp__gallery');
            $this->addSql('CREATE INDEX IDX_472B783A627EA78A ON gallery (main_id)');
            $this->addSql('CREATE INDEX IDX_472B783AA480B5AC ON gallery (separator_id)');
            $this->addSql('CREATE INDEX IDX_472B783AA842F916 ON gallery (show_case_id)');
            $this->addSql('DROP INDEX IDX_B05143234E7AF8F');
            $this->addSql('DROP INDEX IDX_B0514323EA9FDD75');
            $this->addSql('CREATE TEMPORARY TABLE __temp__gallery_medias AS SELECT gallery_id, media_id FROM gallery_medias');
            $this->addSql('DROP TABLE gallery_medias');
            $this->addSql('CREATE TABLE gallery_medias (gallery_id INTEGER NOT NULL, media_id INTEGER NOT NULL, PRIMARY KEY(gallery_id, media_id))');
            $this->addSql('INSERT INTO gallery_medias (gallery_id, media_id) SELECT gallery_id, media_id FROM __temp__gallery_medias');
            $this->addSql('DROP TABLE __temp__gallery_medias');
            $this->addSql('CREATE INDEX IDX_B05143234E7AF8F ON gallery_medias (gallery_id)');
            $this->addSql('CREATE INDEX IDX_B0514323EA9FDD75 ON gallery_medias (media_id)');
            $this->addSql('DROP INDEX IDX_71D60CD0C93D69EA');
            $this->addSql('DROP INDEX IDX_71D60CD0A480B5AC');
            $this->addSql('CREATE TEMPORARY TABLE __temp__home AS SELECT id, background_id, separator_id, title, subtitle FROM home');
            $this->addSql('DROP TABLE home');
            $this->addSql('CREATE TABLE home (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, background_id INTEGER DEFAULT NULL, separator_id INTEGER DEFAULT NULL, title VARCHAR(1024) DEFAULT NULL, subtitle VARCHAR(1024) DEFAULT NULL)');
            $this->addSql('INSERT INTO home (id, background_id, separator_id, title, subtitle) SELECT id, background_id, separator_id, title, subtitle FROM __temp__home');
            $this->addSql('DROP TABLE __temp__home');
            $this->addSql('CREATE INDEX IDX_71D60CD0C93D69EA ON home (background_id)');
            $this->addSql('CREATE INDEX IDX_71D60CD0A480B5AC ON home (separator_id)');
            $this->addSql('DROP INDEX IDX_14D43132DE12AB56');
            $this->addSql('CREATE TEMPORARY TABLE __temp__media_object AS SELECT id, created_by, file_path, description FROM media_object');
            $this->addSql('DROP TABLE media_object');
            $this->addSql('CREATE TABLE media_object (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by INTEGER DEFAULT NULL, file_path VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL)');
            $this->addSql('INSERT INTO media_object (id, created_by, file_path, description) SELECT id, created_by, file_path, description FROM __temp__media_object');
            $this->addSql('DROP TABLE __temp__media_object');
            $this->addSql('CREATE INDEX IDX_14D43132DE12AB56 ON media_object (created_by)');
            $this->addSql('CREATE TEMPORARY TABLE __temp__view_translate AS SELECT "key", translate FROM view_translate');
            $this->addSql('DROP TABLE view_translate');
            $this->addSql('CREATE TABLE view_translate ("key" VARCHAR(128) NOT NULL, translate CLOB DEFAULT NULL COLLATE BINARY, PRIMARY KEY("key"))');
            $this->addSql('INSERT INTO view_translate ("key", translate) SELECT "key", translate FROM __temp__view_translate');
            $this->addSql('DROP TABLE __temp__view_translate');
        }
    }
}
