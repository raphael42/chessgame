<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200417145246 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE player_piece (player_id INT NOT NULL, piece_id INT NOT NULL, INDEX IDX_FBB8706599E6F5DF (player_id), INDEX IDX_FBB87065C40FCFA8 (piece_id), PRIMARY KEY(player_id, piece_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE player_piece ADD CONSTRAINT FK_FBB8706599E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player_piece ADD CONSTRAINT FK_FBB87065C40FCFA8 FOREIGN KEY (piece_id) REFERENCES piece (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE game_pieces');
        $this->addSql('ALTER TABLE piece CHANGE position position VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE game_pieces (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, piece_id INT NOT NULL, INDEX IDX_C418D9BA99E6F5DF (player_id), INDEX IDX_C418D9BAC40FCFA8 (piece_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE game_pieces ADD CONSTRAINT FK_C418D9BA99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE game_pieces ADD CONSTRAINT FK_C418D9BAC40FCFA8 FOREIGN KEY (piece_id) REFERENCES piece (id)');
        $this->addSql('DROP TABLE player_piece');
        $this->addSql('ALTER TABLE piece CHANGE position position VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
    }
}
