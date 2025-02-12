<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211165404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "1", "Le fou", "bishop", "Il se déplace en diagonale", "2", "Collectez toutes les étoiles !", "6*1/8/8/3*4/8/5B2/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "2", "Le fou", "bishop", "Il se déplace en diagonale", "6", "Moins vous effectuez de déplacements, plus vous gagnez de points !", "8/8/8/1*6/8/1B1*4/*3*3/1*1*4 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "3", "Le fou", "bishop", "Il se déplace en diagonale", "6", "Collectez toutes les étoiles !", "8/6*1/1*5*/8/3B4/4*3/8/*1*5 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "4", "Le fou", "bishop", "Il se déplace en diagonale", "6", "Collectez toutes les étoiles !", "8/8/8/8/*1B5/1*1*4/2*1*3/1*6 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "5", "Le fou", "bishop", "Il se déplace en diagonale", "6", "Un fou de cases blanches,<br>Un fou de cases noires.<br>Vous avez besoin des deux !", "8/8/8/3**3/3**3/3**3/8/2B2B2 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "6", "Le fou", "bishop", "Il se déplace en diagonale", "8", "Un fou de cases blanches,<br>Un fou de cases noires.<br>Vous avez besoin des deux !", "6*1/3B*2*/5*2/5*2/7*1/*1B5/2*5/8 w KQkq - 0 1")');


        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "1", "Le Cavalier", "knight", "Il se déplace en forme de L", "2", "Les Cavaliers possèdent une façon élégante de sauter partout !", "8/3*4/8/2*5/4N3/8/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "2", "Le Cavalier", "knight", "Il se déplace en forme de L", "7", "Collectez toutes les étoiles !", "7*/5*2/8/6*1/3*4/2*2*2/4*3/1N6 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "3", "Le Cavalier", "knight", "Il se déplace en forme de L", "5", "Collectez toutes les étoiles !", "8/2N*4/1*2*3/3*4/5*2/8/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "4", "Le Cavalier", "knight", "Il se déplace en forme de L", "9", "Les Cavaliers peuvent sauter par dessus les obstacles pour collecter les étoiles !", "8/8/8/4***1/4*N*1/4***1/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "5", "Le Cavalier", "knight", "Il se déplace en forme de L", "6", "Collectez toutes les étoiles !", "8/8/6*1/8/4**2/2*N4/4**2/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "6", "Le Cavalier", "knight", "Il se déplace en forme de L", "9", "Collectez toutes les étoiles !", "2*5/2N1*3/2*5/1*1*1*2/1*1*4/4*3/8/8 w KQkq - 0 1")');


        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "1", "La dame", "queen", "Dame = tour + fou", "2", "Collectez toutes les étoiles !", "1*6/8/8/4*3/8/8/4Q3/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "2", "La dame", "queen", "Dame = tour + fou", "4", "Collectez toutes les étoiles !", "5*2/8/8/8/3Q4/*6*/5*2/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "3", "La dame", "queen", "Dame = tour + fou", "6", "Collectez toutes les étoiles !", "5*2/8/3*3*/8/2Q5/*5*1/8/5*2 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "5", "La dame", "queen", "Dame = tour + fou", "7", "Collectez toutes les étoiles !", "6*1/6Q1/8/1*5*/8/3*4/*6*/6*1 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "6", "La dame", "queen", "Dame = tour + fou", "8", "Collectez toutes les étoiles !", "6*1/8/*4**1/8/7*/8/5*2/3*Q2* w KQkq - 0 1")');


        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "1", "Le roi", "king", "La pièce la plus importante", "3", "Le Roi se déplace lentement", "8/8/4*3/8/8/3K4/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "2", "Le roi", "king", "La pièce la plus importante", "4", "Collectez toutes les étoiles !", "8/8/8/8/8/3**3/2*1*3/4K3 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "3", "Le roi", "king", "La pièce la plus importante", "6", "Collectez toutes les étoiles !", "8/8/3*4/1**1K3/6*1/4**2/8/8 w KQkq - 0 1")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
