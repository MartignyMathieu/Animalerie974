<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261118115225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Les comptes sont supprimés : on retire d'abord la clé étrangère du panier, puis la table.
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY `FK_24CC0DF2FB88E14F`');
        $this->addSql('DROP INDEX UNIQ_24CC0DF2FB88E14F ON panier');
        $this->addSql('ALTER TABLE panier DROP utilisateur_id');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('ALTER TABLE produit ADD type VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, roles JSON NOT NULL, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, cree_le DATETIME NOT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE panier ADD utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT `FK_24CC0DF2FB88E14F` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_24CC0DF2FB88E14F ON panier (utilisateur_id)');
        $this->addSql('ALTER TABLE produit DROP type');
    }
}
