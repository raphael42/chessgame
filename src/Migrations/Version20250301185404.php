<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301185404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `title`, `slug`, `subtitle`, `score_goal`, `description`, `fen`) VALUES (NULL, "1", "Protéger", "protect", "Assurez la sécurité de vos pièces", "1", "Vous êtes attaqué ! Fuyez la menace !", "8/8/8/4bb2/8/8/P2P4/R2K4 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `title`, `slug`, `subtitle`, `score_goal`, `description`, `fen`) VALUES (NULL, "2", "Protéger", "protect", "Assurez la sécurité de vos pièces", "1", "Vous êtes attaqué ! Fuyez la menace !", "8/8/2q2N2/8/8/8/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `title`, `slug`, `subtitle`, `score_goal`, `description`, `fen`) VALUES (NULL, "3", "Protéger", "protect", "Assurez la sécurité de vos pièces", "1", "Il n\'y a pas d\'échappatoire, mais vous pouvez vous défendre !", "8/N2q4/8/8/8/8/6R1/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `title`, `slug`, `subtitle`, `score_goal`, `description`, `fen`) VALUES (NULL, "4", "Protéger", "protect", "Assurez la sécurité de vos pièces", "1", "Il n\'y a pas d\'échappatoire, mais vous pouvez vous défendre !", "8/8/1Bq5/8/2P5/8/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `title`, `slug`, `subtitle`, `score_goal`, `description`, `fen`) VALUES (NULL, "5", "Protéger", "protect", "Assurez la sécurité de vos pièces", "1", "Il n\'y a pas d\'échappatoire, mais vous pouvez vous défendre !", "1r6/8/5b2/8/8/5N2/P2P4/R1B5 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `title`, `slug`, `subtitle`, `score_goal`, `description`, `fen`) VALUES (NULL, "6", "Protéger", "protect", "Assurez la sécurité de vos pièces", "1", "Ne laissez pas l\'adversaire prendre une pièce non défendue !", "8/1b6/8/8/8/3P2P1/5NRP/r7 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `title`, `slug`, `subtitle`, `score_goal`, `description`, `fen`) VALUES (NULL, "7", "Protéger", "protect", "Assurez la sécurité de vos pièces", "1", "Ne laissez pas l\'adversaire prendre une pièce non défendue !", "rr6/3q4/4n3/4P1B1/7P/P7/1B1N1PP1/R5K1 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `title`, `slug`, `subtitle`, `score_goal`, `description`, `fen`) VALUES (NULL, "8", "Protéger", "protect", "Assurez la sécurité de vos pièces", "1", "Ne laissez pas l\'adversaire prendre une pièce non défendue !", "8/3q4/8/1N3R2/8/2PB4/8/8 w KQkq - 0 1")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
