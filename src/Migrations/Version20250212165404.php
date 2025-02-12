<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212165404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM `challenge` WHERE slug = "king" AND ordering = 3;');

        $this->addSql('INSERT INTO `challenge` (`id`, `ordering`, `category`, `slug`, `name`, `score_goal`, `description`, `fen`) VALUES (NULL, "3", "Le roi", "king", "La pièce la plus importante", "8", "Collectez toutes les étoiles !", "8/8/3*4/1**1K3/6*1/4**2/8/8 w KQkq - 0 1")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
