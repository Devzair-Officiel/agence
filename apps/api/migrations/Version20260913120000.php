<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 9C — Ajout des colonnes variants WebP sur `editorial_media_asset`.
 *
 * Deux paires atomiques (card_* et hero_*), nullables pour la compatibilité
 * avec les assets uploadés avant Phase 9C. Les CHECK constraints garantissent
 * qu'une paire est soit entièrement NULL soit entièrement renseignée.
 *
 * Aucun backfill obligatoire : les anciens assets conservent NULL et le
 * front tombe en fallback sur l'URL original (`/api/media/{uuid}`).
 * Une commande `editorial:media:generate-variants` pourra régénérer les
 * variants à partir de `original.{ext}` sans intervention manuelle.
 */
final class Version20260913120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Phase 9C — Colonnes variants WebP (card + hero) sur editorial_media_asset.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE editorial_media_asset
                ADD COLUMN card_storage_key  VARCHAR(255)  DEFAULT NULL,
                ADD COLUMN card_width        INTEGER       DEFAULT NULL,
                ADD COLUMN card_height       INTEGER       DEFAULT NULL,
                ADD COLUMN card_size_bytes   BIGINT        DEFAULT NULL,
                ADD COLUMN card_sha256       VARCHAR(64)   DEFAULT NULL,
                ADD COLUMN hero_storage_key  VARCHAR(255)  DEFAULT NULL,
                ADD COLUMN hero_width        INTEGER       DEFAULT NULL,
                ADD COLUMN hero_height       INTEGER       DEFAULT NULL,
                ADD COLUMN hero_size_bytes   BIGINT        DEFAULT NULL,
                ADD COLUMN hero_sha256       VARCHAR(64)   DEFAULT NULL
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE editorial_media_asset
                ADD CONSTRAINT chk_editorial_media_asset_card_pair CHECK (
                    (card_storage_key IS NULL AND card_width IS NULL AND card_height IS NULL
                     AND card_size_bytes IS NULL AND card_sha256 IS NULL)
                    OR
                    (card_storage_key IS NOT NULL AND card_width IS NOT NULL AND card_height IS NOT NULL
                     AND card_size_bytes IS NOT NULL AND card_sha256 IS NOT NULL)
                ),
                ADD CONSTRAINT chk_editorial_media_asset_hero_pair CHECK (
                    (hero_storage_key IS NULL AND hero_width IS NULL AND hero_height IS NULL
                     AND hero_size_bytes IS NULL AND hero_sha256 IS NULL)
                    OR
                    (hero_storage_key IS NOT NULL AND hero_width IS NOT NULL AND hero_height IS NOT NULL
                     AND hero_size_bytes IS NOT NULL AND hero_sha256 IS NOT NULL)
                )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE editorial_media_asset
                DROP CONSTRAINT chk_editorial_media_asset_card_pair,
                DROP CONSTRAINT chk_editorial_media_asset_hero_pair
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE editorial_media_asset
                DROP COLUMN card_storage_key,
                DROP COLUMN card_width,
                DROP COLUMN card_height,
                DROP COLUMN card_size_bytes,
                DROP COLUMN card_sha256,
                DROP COLUMN hero_storage_key,
                DROP COLUMN hero_width,
                DROP COLUMN hero_height,
                DROP COLUMN hero_size_bytes,
                DROP COLUMN hero_sha256
        SQL);
    }
}
