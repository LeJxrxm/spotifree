<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301203948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE musique_user (id INT AUTO_INCREMENT NOT NULL, volume DOUBLE PRECISION NOT NULL, musique_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3FE4FD7825E254A1 (musique_id), INDEX IDX_3FE4FD78A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE musique_user ADD CONSTRAINT FK_3FE4FD7825E254A1 FOREIGN KEY (musique_id) REFERENCES musique (id)');
        $this->addSql('ALTER TABLE musique_user ADD CONSTRAINT FK_3FE4FD78A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE musique_user DROP FOREIGN KEY FK_3FE4FD7825E254A1');
        $this->addSql('ALTER TABLE musique_user DROP FOREIGN KEY FK_3FE4FD78A76ED395');
        $this->addSql('DROP TABLE musique_user');
    }
}
