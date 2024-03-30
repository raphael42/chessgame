<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240330170407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact_form (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, subject VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL, message LONGTEXT NOT NULL, date_insert DATETIME NOT NULL, status VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(200) NOT NULL, increment INT NOT NULL, fen VARCHAR(255) NOT NULL, time DOUBLE PRECISION DEFAULT NULL, date_insert DATETIME NOT NULL, status VARCHAR(50) NOT NULL, winner VARCHAR(5) DEFAULT NULL, end_reason VARCHAR(100) DEFAULT NULL, pgn LONGTEXT DEFAULT NULL, type VARCHAR(100) NOT NULL, creator_color_chose VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE moves (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, game_id INT NOT NULL, fen_before VARCHAR(255) NOT NULL, fen_after VARCHAR(255) NOT NULL, piece VARCHAR(10) NOT NULL, square_from VARCHAR(10) NOT NULL, square_to VARCHAR(10) NOT NULL, san VARCHAR(10) DEFAULT NULL, lan VARCHAR(10) DEFAULT NULL, flags VARCHAR(10) DEFAULT NULL, promotion VARCHAR(10) DEFAULT NULL, move_number INT NOT NULL, INDEX IDX_453F083299E6F5DF (player_id), INDEX IDX_453F0832E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, color VARCHAR(20) NOT NULL, game_creator TINYINT(1) NOT NULL, time_left INT DEFAULT NULL, user_agent LONGTEXT DEFAULT NULL, ip VARCHAR(50) DEFAULT NULL, INDEX IDX_98197A65E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE moves ADD CONSTRAINT FK_453F083299E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE moves ADD CONSTRAINT FK_453F0832E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E9699E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96E48FD905');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E9699E6F5DF');
        $this->addSql('ALTER TABLE moves DROP FOREIGN KEY FK_453F083299E6F5DF');
        $this->addSql('ALTER TABLE moves DROP FOREIGN KEY FK_453F0832E48FD905');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65E48FD905');
        $this->addSql('DROP TABLE contact_form');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE moves');
        $this->addSql('DROP TABLE player');
    }
}
