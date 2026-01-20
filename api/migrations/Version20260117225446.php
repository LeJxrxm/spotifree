<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260117225446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE album (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, cover_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, artiste_id INT DEFAULT NULL, INDEX IDX_39986E4321D25844 (artiste_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE artiste (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, image_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE musique (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, duree INT NOT NULL, audio_url VARCHAR(255) NOT NULL, track_number INT DEFAULT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, artiste_id INT DEFAULT NULL, album_id INT DEFAULT NULL, INDEX IDX_EE1D56BC21D25844 (artiste_id), INDEX IDX_EE1D56BC1137ABCF (album_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, pseudo VARCHAR(100) NOT NULL, avatar_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE album ADD CONSTRAINT FK_39986E4321D25844 FOREIGN KEY (artiste_id) REFERENCES artiste (id)');
        $this->addSql('ALTER TABLE musique ADD CONSTRAINT FK_EE1D56BC21D25844 FOREIGN KEY (artiste_id) REFERENCES artiste (id)');
        $this->addSql('ALTER TABLE musique ADD CONSTRAINT FK_EE1D56BC1137ABCF FOREIGN KEY (album_id) REFERENCES album (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE album DROP FOREIGN KEY FK_39986E4321D25844');
        $this->addSql('ALTER TABLE musique DROP FOREIGN KEY FK_EE1D56BC21D25844');
        $this->addSql('ALTER TABLE musique DROP FOREIGN KEY FK_EE1D56BC1137ABCF');
        $this->addSql('DROP TABLE album');
        $this->addSql('DROP TABLE artiste');
        $this->addSql('DROP TABLE musique');
        $this->addSql('DROP TABLE `user`');
    }
}
