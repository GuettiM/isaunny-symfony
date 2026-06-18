<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Schéma initial du blog I.sAunny (conforme au MCD).
 *
 * Tables : T_CATEGORIE, T_MEMBRE, T_ARTICLE, T_COMMENTAIRE.
 *
 * NOTE : si ta base "isaunny" contient DÉJÀ ces tables (projet PHP natif existant),
 * tu n'as PAS besoin de lancer cette migration : marque-la simplement comme déjà
 * appliquée avec
 *     php bin/console doctrine:migrations:version --add --all
 * Cette migration sert à recréer la base de zéro sur une nouvelle machine.
 */
final class Version20260101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création du schéma initial : T_CATEGORIE, T_MEMBRE, T_ARTICLE, T_COMMENTAIRE.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\AbstractMySQLPlatform,
            'Cette migration vise MySQL / MariaDB.'
        );

        // --- T_CATEGORIE ---
        $this->addSql('CREATE TABLE T_CATEGORIE (
            id_categorie INT AUTO_INCREMENT NOT NULL,
            nom VARCHAR(255) NOT NULL,
            PRIMARY KEY (id_categorie)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // --- T_MEMBRE ---
        $this->addSql('CREATE TABLE T_MEMBRE (
            id_membre INT AUTO_INCREMENT NOT NULL,
            pseudo VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) NOT NULL,
            UNIQUE INDEX uniq_membre_email (email),
            PRIMARY KEY (id_membre)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // --- T_ARTICLE ---
        $this->addSql('CREATE TABLE T_ARTICLE (
            id_article INT AUTO_INCREMENT NOT NULL,
            id_categorie INT NOT NULL,
            titre VARCHAR(255) NOT NULL,
            description LONGTEXT NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            date DATE DEFAULT NULL,
            INDEX idx_article_categorie (id_categorie),
            PRIMARY KEY (id_article)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // --- T_COMMENTAIRE ---
        $this->addSql('CREATE TABLE T_COMMENTAIRE (
            id_commentaire INT AUTO_INCREMENT NOT NULL,
            id_membre INT NOT NULL,
            id_article INT NOT NULL,
            contenu LONGTEXT NOT NULL,
            date_commentaire DATETIME DEFAULT NULL,
            statut VARCHAR(50) NOT NULL,
            INDEX idx_commentaire_membre (id_membre),
            INDEX idx_commentaire_article (id_article),
            PRIMARY KEY (id_commentaire)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // --- Clés étrangères ---
        $this->addSql('ALTER TABLE T_ARTICLE
            ADD CONSTRAINT fk_article_categorie
            FOREIGN KEY (id_categorie) REFERENCES T_CATEGORIE (id_categorie)');

        $this->addSql('ALTER TABLE T_COMMENTAIRE
            ADD CONSTRAINT fk_commentaire_membre
            FOREIGN KEY (id_membre) REFERENCES T_MEMBRE (id_membre)');

        $this->addSql('ALTER TABLE T_COMMENTAIRE
            ADD CONSTRAINT fk_commentaire_article
            FOREIGN KEY (id_article) REFERENCES T_ARTICLE (id_article)');
    }

    public function down(Schema $schema): void
    {
        // Ordre inverse pour respecter les contraintes.
        $this->addSql('ALTER TABLE T_COMMENTAIRE DROP FOREIGN KEY fk_commentaire_article');
        $this->addSql('ALTER TABLE T_COMMENTAIRE DROP FOREIGN KEY fk_commentaire_membre');
        $this->addSql('ALTER TABLE T_ARTICLE DROP FOREIGN KEY fk_article_categorie');
        $this->addSql('DROP TABLE T_COMMENTAIRE');
        $this->addSql('DROP TABLE T_ARTICLE');
        $this->addSql('DROP TABLE T_MEMBRE');
        $this->addSql('DROP TABLE T_CATEGORIE');
    }
}
