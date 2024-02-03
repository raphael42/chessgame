<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240131200606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE moves ADD game_id INT NOT NULL, CHANGE san san VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE moves ADD CONSTRAINT FK_453F0832E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('CREATE INDEX IDX_453F0832E48FD905 ON moves (game_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE moves DROP FOREIGN KEY FK_453F0832E48FD905');
        $this->addSql('DROP INDEX IDX_453F0832E48FD905 ON moves');
        $this->addSql('ALTER TABLE moves DROP game_id, CHANGE san san VARCHAR(10) DEFAULT NULL');
    }
}
