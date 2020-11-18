<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201118230239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'sqlite') {
            // this up() migration is auto-generated, please modify it to your needs
            $this->addSql('CREATE TABLE about (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, background_id INTEGER DEFAULT NULL, separator_id INTEGER DEFAULT NULL, about VARCHAR(1024) DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL)');
            $this->addSql('CREATE INDEX IDX_B5F422E3C93D69EA ON about (background_id)');
            $this->addSql('CREATE INDEX IDX_B5F422E3A480B5AC ON about (separator_id)');
            $this->addSql('CREATE TABLE contact (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, background_id INTEGER DEFAULT NULL, separator_id INTEGER DEFAULT NULL, contact VARCHAR(1024) DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL)');
            $this->addSql('CREATE INDEX IDX_4C62E638C93D69EA ON contact (background_id)');
            $this->addSql('CREATE INDEX IDX_4C62E638A480B5AC ON contact (separator_id)');
            $this->addSql('CREATE TABLE ext_translations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, object_class VARCHAR(255) NOT NULL, field VARCHAR(32) NOT NULL, foreign_key VARCHAR(64) NOT NULL, content CLOB DEFAULT NULL)');
            $this->addSql('CREATE INDEX translations_lookup_idx ON ext_translations (locale, object_class, foreign_key)');
            $this->addSql('CREATE UNIQUE INDEX lookup_unique_idx ON ext_translations (locale, object_class, field, foreign_key)');
            $this->addSql('CREATE TABLE gallery (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, main_id INTEGER DEFAULT NULL, separator_id INTEGER DEFAULT NULL, show_case_id INTEGER DEFAULT NULL, title VARCHAR(1024) DEFAULT NULL, order_ INTEGER NOT NULL)');
            $this->addSql('CREATE INDEX IDX_472B783A627EA78A ON gallery (main_id)');
            $this->addSql('CREATE INDEX IDX_472B783AA480B5AC ON gallery (separator_id)');
            $this->addSql('CREATE INDEX IDX_472B783AA842F916 ON gallery (show_case_id)');
            $this->addSql('CREATE TABLE gallery_medias (gallery_id INTEGER NOT NULL, media_id INTEGER NOT NULL, PRIMARY KEY(gallery_id, media_id))');
            $this->addSql('CREATE INDEX IDX_B05143234E7AF8F ON gallery_medias (gallery_id)');
            $this->addSql('CREATE INDEX IDX_B0514323EA9FDD75 ON gallery_medias (media_id)');
            $this->addSql('CREATE TABLE home (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, background_id INTEGER DEFAULT NULL, separator_id INTEGER DEFAULT NULL, title VARCHAR(1024) DEFAULT NULL, subtitle VARCHAR(1024) DEFAULT NULL)');
            $this->addSql('CREATE INDEX IDX_71D60CD0C93D69EA ON home (background_id)');
            $this->addSql('CREATE INDEX IDX_71D60CD0A480B5AC ON home (separator_id)');
            $this->addSql('CREATE TABLE media_object (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by INTEGER DEFAULT NULL, file_path VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL)');
            $this->addSql('CREATE INDEX IDX_14D43132DE12AB56 ON media_object (created_by)');
            $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(64) NOT NULL, email VARCHAR(64) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, created DATETIME DEFAULT NULL)');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON "user" (username)');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
            $this->addSql('CREATE TABLE view_translate ("key" VARCHAR(128) NOT NULL, translate CLOB DEFAULT NULL, PRIMARY KEY("key"))');
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() === 'sqlite') {
            // this down() migration is auto-generated, please modify it to your needs
            $this->addSql('DROP TABLE about');
            $this->addSql('DROP TABLE contact');
            $this->addSql('DROP TABLE ext_translations');
            $this->addSql('DROP TABLE gallery');
            $this->addSql('DROP TABLE gallery_medias');
            $this->addSql('DROP TABLE home');
            $this->addSql('DROP TABLE media_object');
            $this->addSql('DROP TABLE "user"');
            $this->addSql('DROP TABLE view_translate');
        }
    }
}
