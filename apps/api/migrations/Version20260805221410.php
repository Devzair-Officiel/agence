<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 8C1 — Création de la table `admin_user` (Fondation identité).
 *
 * Choix structurants :
 * - UUID stocké en `uuid` natif Postgres (généré côté PHP via `Uuid::v7()`).
 * - `normalized_email` (lowercase + trim) porte la contrainte UNIQUE — c'est
 *   la clé de recherche du `UserProvider`. `email` conserve la casse
 *   d'origine pour l'affichage.
 * - Pas de colonne `roles` : le rôle unique implicite est `ROLE_ADMIN`
 *   (voir `AdminUser::getRoles()`). Ajouter une colonne JSON aujourd'hui
 *   serait une abstraction sans usage — elle sera introduite quand un
 *   deuxième rôle apparaîtra.
 * - `is_active BOOLEAN NOT NULL DEFAULT true` : porte l'invariant côté
 *   base (default), l'agrégat le porte côté domaine.
 * - `last_login_at NULLABLE` : nul avant la première connexion.
 */
final class Version20260805221410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Phase 8C1 — Crée la table admin_user (PostgreSQL 17).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE admin_user (
                id UUID NOT NULL,
                email VARCHAR(180) NOT NULL,
                normalized_email VARCHAR(180) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                display_name VARCHAR(120) NOT NULL,
                is_active BOOLEAN NOT NULL DEFAULT true,
                last_login_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql('CREATE UNIQUE INDEX uniq_admin_user_normalized_email ON admin_user (normalized_email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE admin_user');
    }
}
