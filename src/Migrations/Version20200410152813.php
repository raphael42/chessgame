<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200410152813 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(200) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game_pieces (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, piece_id INT NOT NULL, INDEX IDX_C418D9BA99E6F5DF (player_id), INDEX IDX_C418D9BAC40FCFA8 (piece_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE piece (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(20) NOT NULL, position VARCHAR(10) DEFAULT NULL, is_taken TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, color VARCHAR(20) NOT NULL, INDEX IDX_98197A65E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE timer (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, time DOUBLE PRECISION NOT NULL, INDEX IDX_6AD0DE1A99E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE game_pieces ADD CONSTRAINT FK_C418D9BA99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE game_pieces ADD CONSTRAINT FK_C418D9BAC40FCFA8 FOREIGN KEY (piece_id) REFERENCES piece (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE timer ADD CONSTRAINT FK_6AD0DE1A99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65E48FD905');
        $this->addSql('ALTER TABLE game_pieces DROP FOREIGN KEY FK_C418D9BAC40FCFA8');
        $this->addSql('ALTER TABLE game_pieces DROP FOREIGN KEY FK_C418D9BA99E6F5DF');
        $this->addSql('ALTER TABLE timer DROP FOREIGN KEY FK_6AD0DE1A99E6F5DF');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE game_pieces');
        $this->addSql('DROP TABLE piece');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE timer');
    }
}
