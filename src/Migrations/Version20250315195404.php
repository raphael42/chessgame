<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250315195404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE `challenge` SET `fen` = '6*1/3B*2*/5*2/5*2/7*/*1B5/2*5/8 w - - 0 1' WHERE `id` = '20';");
        $this->addSql("UPDATE `challenge` SET `fen` = '2rb4/2k5/5N2/1Q6/8/8/8/8 w - - 0 1' WHERE `id` = '141';");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
