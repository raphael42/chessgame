<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250402211359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE challenge_section (id INT AUTO_INCREMENT NOT NULL, ordering INT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE challenge_category ADD challenge_section_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE challenge_category ADD CONSTRAINT FK_9B72FE21B833FA73 FOREIGN KEY (challenge_section_id) REFERENCES challenge_section (id)');
        $this->addSql('CREATE INDEX IDX_9B72FE21B833FA73 ON challenge_category (challenge_section_id)');

        $this->addSql('INSERT INTO challenge_section (ordering, label) VALUES (1, \'Les pièces\'), (2, \'Les fondamentaux\'), (3, \'Intermédiaire\'), (4, \'Avancé\')');
        $this->addSql('UPDATE challenge_category SET challenge_section_id = 1 WHERE id IN (1, 2, 3, 4, 5, 6)');
        $this->addSql('UPDATE challenge_category SET challenge_section_id = 2 WHERE id IN (7, 8, 9, 10, 11, 12)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE challenge_section');
        $this->addSql('ALTER TABLE challenge_category DROP FOREIGN KEY FK_9B72FE21B833FA73');
        $this->addSql('DROP INDEX IDX_9B72FE21B833FA73 ON challenge_category');
        $this->addSql('ALTER TABLE challenge_category DROP challenge_section_id');
        $this->addSql('DELETE FROM challenge_section WHERE id IN (1, 2, 3, 4)');
        $this->addSql('UPDATE challenge_category SET challenge_section_id = NULL WHERE id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12)');
    }
}
