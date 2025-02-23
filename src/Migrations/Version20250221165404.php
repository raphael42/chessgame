<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221165404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "1", "Capturer", "capture", "Prenez les pièces adverses", "2", "Prenez les pièces noires !", "8/2p2p2/8/8/8/2R5/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "2", "Capturer", "capture", "Prenez les pièces adverses", "2", "Prenez les pièces noires !<br>Et ne perdez pas les vôtres.", "8/2r2p2/8/8/5Q2/8/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "3", "Capturer", "capture", "Prenez les pièces adverses", "5", "Prenez les pièces noires !<br>Et ne perdez pas les vôtres.", "8/5r2/8/1r3p2/8/3B4/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "4", "Capturer", "capture", "Prenez les pièces adverses", "7", "Prenez les pièces noires !<br>Et ne perdez pas les vôtres.", "8/5b2/5p2/3n2p1/8/6Q1/8/8 w KQkq - 0 1")');
        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "5", "Capturer", "capture", "Prenez les pièces adverses", "6", "Prenez les pièces noires !<br>Et ne perdez pas les vôtres.", "8/3b4/2p2q2/8/3p1N2/8/8/8 w KQkq - 0 1")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
