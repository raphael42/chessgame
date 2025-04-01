<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401194814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE challenge_category (id INT AUTO_INCREMENT NOT NULL, ordering INT NOT NULL, title VARCHAR(255) NOT NULL, subtitle VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE challenge ADD challenge_category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE challenge ADD CONSTRAINT FK_D70989516BFA355F FOREIGN KEY (challenge_category_id) REFERENCES challenge_category (id)');
        $this->addSql('CREATE INDEX IDX_D70989516BFA355F ON challenge (challenge_category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE challenge_category');
        $this->addSql('ALTER TABLE challenge DROP FOREIGN KEY FK_D70989516BFA355F');
        $this->addSql('DROP INDEX IDX_D70989516BFA355F ON challenge');
        $this->addSql('ALTER TABLE challenge DROP challenge_category_id');
    }
}
