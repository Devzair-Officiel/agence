<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 9A — Création de la table `editorial_media_asset` (Fondation média).
 *
 * Objectif : persister uniquement l'identité et les métadonnées serveur d'un
 * fichier image téléversé, sans référence à un `Article`. Le rattachement à
 * un article, le texte alternatif contextuel et l'exposition publique sont
 * livrés en Phase 9B ; en 9A on n'introduit ni FK vers `editorial_article`
 * ni statut de publication.
 *
 * Choix structurants :
 * - UUID (v7) natif PostgreSQL, généré côté PHP (aligné DEV-034 / DEV-055).
 * - `storage_key` UNIQUE : chemin logique dans le stockage privé
 *   (`{uuid}/original.{ext}`). Toujours contrôlé serveur — jamais dérivé du
 *   nom d'origine (voir DEC-XXX / policy MediaUploadPolicy).
 * - `original_filename` : uniquement une métadonnée d'affichage ; non
 *   indexée, aucune contrainte d'unicité.
 * - `mime_type` : celui déterminé par `finfo_file` sur le fichier
 *   NORMALISÉ (jamais l'entête HTTP client). Restreint aux types réellement
 *   supportés (image/jpeg, image/png, image/webp).
 * - `width`, `height`, `size_bytes` : mesurés sur le fichier normalisé.
 * - `sha256` : calculé sur le fichier normalisé, 64 caractères hex.
 * - `created_at` : `TIMESTAMPTZ` NOT NULL, aligné sur `admin_user` et
 *   `editorial_article`.
 * - Un seul index chronologique (`created_at DESC, id DESC`) pour la
 *   pagination stable de la bibliothèque admin. Pas d'index full-text 9A.
 */
final class Version20260808120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Phase 9A — Crée la table editorial_media_asset (PostgreSQL 17).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE editorial_media_asset (
                id UUID NOT NULL,
                storage_key VARCHAR(255) NOT NULL,
                original_filename VARCHAR(255) NOT NULL,
                mime_type VARCHAR(64) NOT NULL,
                size_bytes BIGINT NOT NULL,
                width INT NOT NULL,
                height INT NOT NULL,
                sha256 VARCHAR(64) NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql('CREATE UNIQUE INDEX uniq_editorial_media_asset_storage_key ON editorial_media_asset (storage_key)');
        $this->addSql('CREATE INDEX idx_editorial_media_asset_created_at ON editorial_media_asset (created_at DESC, id DESC)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE editorial_media_asset');
    }
}
