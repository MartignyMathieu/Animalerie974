<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Schéma initial pour SQLite (production sur Fly.io).
 *
 * Les migrations du dossier « migrations/ » sont écrites pour MySQL (développement local) ;
 * en production (APP_ENV=prod) c'est ce dossier qui est utilisé (voir config/packages/doctrine_migrations.yaml).
 * Quand une entité change : régénérer le SQL avec
 *   DATABASE_URL="sqlite:///%kernel.project_dir%/var/gen.db" php bin/console doctrine:schema:create --dump-sql
 * puis ajouter une nouvelle migration ici.
 */
final class Version20261118120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Schéma initial SQLite : catalogue, comptes, panier et messages de contact';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE categorie (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, slug VARCHAR(120) NOT NULL, image VARCHAR(255) DEFAULT NULL, parent_id INTEGER DEFAULT NULL, CONSTRAINT FK_497DD634727ACA70 FOREIGN KEY (parent_id) REFERENCES categorie (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_497DD634989D9B62 ON categorie (slug)');
        $this->addSql('CREATE INDEX IDX_497DD634727ACA70 ON categorie (parent_id)');
        $this->addSql('CREATE TABLE produit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, slug VARCHAR(150) NOT NULL, description CLOB DEFAULT NULL, prix NUMERIC(8, 2) NOT NULL, prix_promo NUMERIC(8, 2) DEFAULT NULL, stock INTEGER NOT NULL, image VARCHAR(255) DEFAULT NULL, categorie_id INTEGER NOT NULL, CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_29A5EC27989D9B62 ON produit (slug)');
        $this->addSql('CREATE INDEX IDX_29A5EC27BCF5E72D ON produit (categorie_id)');
        $this->addSql('CREATE TABLE utilisateur (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, nom VARCHAR(100) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, cree_le DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur (email)');
        $this->addSql('CREATE TABLE panier (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, token VARCHAR(64) NOT NULL, cree_le DATETIME NOT NULL, modifie_le DATETIME NOT NULL, utilisateur_id INTEGER DEFAULT NULL, CONSTRAINT FK_24CC0DF2FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_24CC0DF25F37A13B ON panier (token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_24CC0DF2FB88E14F ON panier (utilisateur_id)');
        $this->addSql('CREATE TABLE ligne_panier (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, quantite INTEGER NOT NULL, panier_id INTEGER NOT NULL, produit_id INTEGER NOT NULL, CONSTRAINT FK_21691B4F77D927C FOREIGN KEY (panier_id) REFERENCES panier (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_21691B4F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_21691B4F77D927CF347EFB ON ligne_panier (panier_id, produit_id)');
        $this->addSql('CREATE INDEX IDX_21691B4F77D927C ON ligne_panier (panier_id)');
        $this->addSql('CREATE INDEX IDX_21691B4F347EFB ON ligne_panier (produit_id)');
        $this->addSql('CREATE TABLE message_contact (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, sujet VARCHAR(150) NOT NULL, message CLOB NOT NULL, cree_le DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE ligne_panier');
        $this->addSql('DROP TABLE panier');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE message_contact');
    }
}
