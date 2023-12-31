<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230706143118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE interventionfichemonteur ADD intervention_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE interventionfichemonteur ADD CONSTRAINT FK_A89440288EAE3863 FOREIGN KEY (intervention_id) REFERENCES InterventionMonteurs (id)');
        $this->addSql('CREATE INDEX IDX_A89440288EAE3863 ON interventionfichemonteur (intervention_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE InterventionFicheMonteur DROP FOREIGN KEY FK_A89440288EAE3863');
        $this->addSql('DROP INDEX IDX_A89440288EAE3863 ON InterventionFicheMonteur');
        $this->addSql('ALTER TABLE InterventionFicheMonteur DROP intervention_id');
    }
}
