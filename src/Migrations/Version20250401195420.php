<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401195420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        // Insert new challenge categories
        $this->addSql("INSERT INTO `challenge_category` (`id`, `ordering`, `title`, `subtitle`, `slug`) VALUES
            (1,	1,	'La tour',	'Elle se déplace sur une ligne droite',	'rook'),
            (2,	2,	'Le fou',	'Il se déplace en diagonale',	'bishop'),
            (3,	3,	'La dame',	'Dame = tour + fou',	'queen'),
            (4,	4,	'Le roi',	'La pièce la plus importante',	'king'),
            (5,	5,	'Le Cavalier',	'Il se déplace en forme de L',	'knight'),
            (6,	6,	'Le pion',	'Il se déplace uniquement vers l\'avant',	'pawn'),
            (7,	7,	'Capturer',	'Prenez les pièces adverses',	'capture'),
            (8,	8,	'Protéger',	'Assurez la sécurité de vos pièces',	'protect'),
            (9,	9,	'Combat',	'Capturez et défendez les pièces',	'fight'),
            (10,	10,	'Échec en un coup',	'Attaquez le Roi adverse',	'check'),
            (11,	11,	'Parer un échec',	'Défendez votre Roi',	'outcheck'),
            (12,	12,	'Mat en un coup',	'Matez le Roi adverse',	'checkmate');"
        );

        // Update existing challenges with new category IDs
        $this->addSql("UPDATE `challenge` SET `challenge_category_id` = '1' WHERE `slug` = 'rook';");
        $this->addSql("UPDATE `challenge` SET `challenge_category_id` = '2' WHERE `slug` = 'bishop';");
        $this->addSql("UPDATE `challenge` SET `challenge_category_id` = '3' WHERE `slug` = 'queen';");
        $this->addSql("UPDATE `challenge` SET `challenge_category_id` = '4' WHERE `slug` = 'king';");
        $this->addSql("UPDATE `challenge` SET `challenge_category_id` = '5' WHERE `slug` = 'knight';");
        $this->addSql("UPDATE `challenge` SET `challenge_category_id` = '6' WHERE `slug` = 'pawn';");
        $this->addSql("UPDATE `challenge` SET `challenge_category_id` = '7' WHERE `slug` = 'capture';");
        $this->addSql("UPDATE `challenge` SET `challenge_category_id` = '8' WHERE `slug` = 'protect';");
        $this->addSql("UPDATE `challenge` SET `challenge_category_id` = '9' WHERE `slug` = 'fight';");
        $this->addSql("UPDATE `challenge` SET `challenge_category_id` = '10' WHERE `slug` = 'check';");
        $this->addSql("UPDATE `challenge` SET `challenge_category_id` = '11' WHERE `slug` = 'outcheck';");
        $this->addSql("UPDATE `challenge` SET `challenge_category_id` = '12' WHERE `slug` = 'checkmate';");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
