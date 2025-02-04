<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250204220023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "1", "La tour", "Elle se déplace sur une ligne droite", "1", "Cliquer sur la tour et la déplacer jusqu\'à l\'étoile!", "8/4r3/8/8/8/8/4*3/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "2", "La tour", "Elle se déplace sur une ligne droite", "2", "Collectez toutes les étoiles !", "8/8/8/8/2*3*1/8/2r5/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "3", "La tour", "Elle se déplace sur une ligne droite", "3", "Moins vous effectuez de déplacements, plus vous gagnez de points !", "8/8/6*1/*2r2*1/8/8/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "4", "La tour", "Elle se déplace sur une ligne droite", "5", "Moins vous effectuez de déplacements, plus vous gagnez de points !", "6*1/8/8/8/8/8/6**/5**r w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "5", "La tour", "Elle se déplace sur une ligne droite", "4", "Utiliser 2 Tours pour collecter toutes les étoiles !", "8/8/6*1/*2r3*/8/8/1r4*1/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "6", "La tour", "Elle se déplace sur une ligne droite", "7", "Utiliser 2 Tours pour collecter toutes les étoiles !", "r2*4/5*2/5r2/6*1/3*4/8/1*3**1/8 w KQkq - 0 1")');


        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "1", "Le pion", "Il se déplace uniquement vers l\'avant", "4", "Les pions avancent d\'une case vers l\'avant uniquement.<br>Mais, lorsqu\'ils atteignent le bord opposé de l\'échiquier, ils deviennent une pièce plus puissante : c\'est une promotion !", "8/8/5*2/8/p7/8/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "2", "Le pion", "Il se déplace uniquement vers l\'avant", "8", "La plupart du temps, la promotion en Dame est la plus avantageuse.<br>Mais, parfois, une promotion en Cavalier peut s\'avérer utile !",
        "8/8/8/2*5/4*p2/1*6/3*4/*5 w KQkq - 0 1"
        )');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "3", "Le pion", "Il se déplace uniquement vers l\'avant", "4", "Les pions se déplacent vers l\'avant, mais capturent en diagonale !", "8/8/4p3/8/3*4/2*5/3*4/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "4", "Le pion", "Il se déplace uniquement vers l\'avant", "8", "Capturez, puis effectuez une promotion !", "8/8/1p6/1**5/8/1***4/2*5/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "5", "Le pion", "Il se déplace uniquement vers l\'avant", "8", "Capturez, puis effectuez une promotion !", "8/8/3p4/2*5/1*1*4/1*2*3/3*4/2*5 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "6", "Le pion", "Il se déplace uniquement vers l\'avant", "7", "Utilisez tous vos pions !<br>Inutile d\'effectuer des promotions.", "8/8/p1pp3p/3*2*1/1**1*3/8/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "7", "Le pion", "Il se déplace uniquement vers l\'avant", "3", "Un pion sur sa case de départ peut se déplacer initialement de 2 cases d\'un coup !", "8/4p3/8/8/8/3*4/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "8", "Le pion", "Il se déplace uniquement vers l\'avant", "9", "Collecter toutes les étoiles ! Inutile d\'effectuer des promotions.", "8/2pppp2/3*4/4*3/2****2/8/8/8 w KQkq - 0 1")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
