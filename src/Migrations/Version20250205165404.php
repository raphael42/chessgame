<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250205165404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('TRUNCATE TABLE challenge');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');

        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "1", "La tour", "rook", "Elle se déplace sur une ligne droite", "1", "Cliquer sur la tour et la déplacer jusqu\'à l\'étoile!",
        "8/4*3/8/8/8/8/4R3/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "2", "La tour", "rook", "Elle se déplace sur une ligne droite", "2", "Collectez toutes les étoiles !", "8/2R5/8/2*3*1/8/8/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "3", "La tour", "rook", "Elle se déplace sur une ligne droite", "3", "Moins vous effectuez de déplacements, plus vous gagnez de points !", "8/8/8/8/*2R2*1/6*1/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "4", "La tour", "rook", "Elle se déplace sur une ligne droite", "5", "Moins vous effectuez de déplacements, plus vous gagnez de points !", "5**R/6**/8/8/8/8/8/6*2 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "5", "La tour", "rook", "Elle se déplace sur une ligne droite", "4", "Utiliser 2 Tours pour collecter toutes les étoiles !", "8/1R4*1/8/8/*2R3*/6*1/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "6", "La tour", "rook", "Elle se déplace sur une ligne droite", "7", "Utiliser 2 Tours pour collecter toutes les étoiles !", "8/1*3**1/8/3*4/6*1/5R2/5*2/R2*4 w KQkq - 0 1")');


        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "1", "Le pion", "pawn", "Il se déplace uniquement vers l\'avant", "4", "Les pions avancent d\'une case vers l\'avant uniquement.<br>Mais, lorsqu\'ils atteignent le bord opposé de l\'échiquier, ils deviennent une pièce plus puissante : c\'est une promotion !", "8/8/8/P7/8/5*2/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "2", "Le pion", "pawn", "Il se déplace uniquement vers l\'avant", "8", "La plupart du temps, la promotion en Dame est la plus avantageuse.<br>Mais, parfois, une promotion en Cavalier peut s\'avérer utile!", "*7/3*4/1*6/4*P2/2*5/8/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "3", "Le pion", "pawn", "Il se déplace uniquement vers l\'avant", "4", "Les pions se déplacent vers l\'avant, mais capturent en diagonale !", "8/3*4/2*5/3*4/8/4P3/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "4", "Le pion", "pawn", "Il se déplace uniquement vers l\'avant", "8", "Capturez, puis effectuez une promotion !", "8/2*5/1***4/8/1**5/1P6/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "5", "Le pion", "pawn", "Il se déplace uniquement vers l\'avant", "8", "Capturez, puis effectuez une promotion !", "2*5/3*4/1*2*3/1*1*4/2*5/3P4/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "6", "Le pion", "pawn", "Il se déplace uniquement vers l\'avant", "7", "Utilisez tous vos pions !<br>Inutile d\'effectuer des promotions.", "8/8/8/1**1*3/3*2*1/P1PP3P/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "7", "Le pion", "pawn", "Il se déplace uniquement vers l\'avant", "3", "Un pion sur sa case de départ peut se déplacer initialement de 2 cases d\'un coup !", "8/8/3*4/8/8/8/4P3/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "8", "Le pion", "pawn", "Il se déplace uniquement vers l\'avant", "9", "Collecter toutes les étoiles ! Inutile d\'effectuer des promotions.", "8/8/8/2****2/4*3/3*4/2PPPP2/8 w KQkq - 0 1")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
