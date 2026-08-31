<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * EST-8 — Table `estimator_pricing_configuration` : grilles tarifaires versionnées.
 *
 * Invariants garantis par le schéma :
 * - `uniq_pricing_version` : deux versions ne peuvent pas avoir le même label.
 * - `uniq_pricing_one_published` (index partiel) : au plus UNE version peut avoir
 *   le statut `published` à tout moment (contrainte DB-level, pas seulement UI).
 * - `configuration` JSONB contient un snapshot complet et autonome (base_amounts,
 *   scale_percents, feature_complexity, complexity_amounts, content_amounts,
 *   visibility_amounts, recurring_amounts).
 *
 * Seed : la grille tarifaire Devzair `2026-v1` est insérée immédiatement avec le
 * statut `published`, exactement à parité avec `DevzairPricingCatalogV1.php`.
 * Tous les montants sont en centimes EUR (int).
 */
final class Version20260831000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "EST-8 — Crée la table estimator_pricing_configuration et insère la grille tarifaire 2026-v1 (published).";
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE estimator_pricing_configuration (
                id           UUID          NOT NULL,
                version      VARCHAR(80)   NOT NULL,
                status       VARCHAR(20)   NOT NULL DEFAULT 'draft',
                currency     VARCHAR(3)    NOT NULL,
                configuration JSONB        NOT NULL,
                created_by   UUID          DEFAULT NULL,
                published_by UUID          DEFAULT NULL,
                created_at   TIMESTAMPTZ   NOT NULL DEFAULT now(),
                updated_at   TIMESTAMPTZ   NOT NULL DEFAULT now(),
                published_at TIMESTAMPTZ   DEFAULT NULL,
                PRIMARY KEY (id)
            )
        SQL);

        $this->addSql('CREATE UNIQUE INDEX uniq_pricing_version ON estimator_pricing_configuration (version)');

        // Garantit qu'au plus une version peut être publiée simultanément (DB-level).
        $this->addSql(
            "CREATE UNIQUE INDEX uniq_pricing_one_published ON estimator_pricing_configuration (status) WHERE status = 'published'"
        );

        $this->addSql('CREATE INDEX idx_pricing_status ON estimator_pricing_configuration (status)');
        $this->addSql('CREATE INDEX idx_pricing_created_at ON estimator_pricing_configuration (created_at DESC)');

        // ── Seed : grille tarifaire Devzair 2026-v1 ──────────────────────────
        // Parité exacte avec DevzairPricingCatalogV1.php.
        // UUID fixe déterministe pour cette version canonique.
        $config = json_encode([
            'base_amounts' => [
                'vitrinesite' => ['min' => 90_000,  'max' => 130_000],
                'ecommerce'   => ['min' => 170_000, 'max' => 250_000],
                'businessapp' => ['min' => 250_000, 'max' => 400_000],
                'refonte'     => ['min' => 70_000,  'max' => 130_000],
            ],
            'scale_percents' => [
                'small'   => ['min' => 100, 'max' => 100],
                'medium'  => ['min' => 115, 'max' => 120],
                'large'   => ['min' => 130, 'max' => 145],
                'unknown' => ['min' => 100, 'max' => 100],
            ],
            'feature_complexity' => [
                'contact_form'          => 'light',
                'multilingual'          => 'light',
                'content_management'    => 'light',
                'booking_form'          => 'medium',
                'payment'               => 'medium',
                'shipping'              => 'medium',
                'customer_accounts'     => 'medium',
                'notifications'         => 'medium',
                'import_export'         => 'medium',
                'stock_management'      => 'medium',
                'promotions'            => 'medium',
                'authentication'        => 'advanced',
                'roles_and_permissions' => 'advanced',
                'document_generation'   => 'advanced',
                'api_integration'       => 'advanced',
            ],
            'complexity_amounts' => [
                'light'    => ['min' => 10_000,  'max' => 35_000],
                'medium'   => ['min' => 35_000,  'max' => 80_000],
                'advanced' => ['min' => 70_000,  'max' => 180_000],
            ],
            'content_amounts' => [
                'visual_identity_needed'  => ['min' => 50_000,  'max' => 100_000],
                'visual_identity_partial' => ['min' => 25_000,  'max' => 50_000],
                'content_to_write'        => ['min' => 40_000,  'max' => 90_000],
                'content_with_help'       => ['min' => 15_000,  'max' => 40_000],
                'photography_needed'      => ['min' => 25_000,  'max' => 60_000],
            ],
            'visibility_amounts' => [
                'seo_basic'          => null,
                'seo_advanced'       => ['min' => 30_000, 'max' => 80_000],
                'local_visibility'   => ['min' => 20_000, 'max' => 50_000],
                'editorial_strategy' => ['min' => 30_000, 'max' => 70_000],
            ],
            'recurring_amounts' => [
                'ESSENTIAL_MAINTENANCE' => ['min' => 4_900,  'max' => 6_900],
                'MAINTENANCE_FOLLOWUP'  => ['min' => 8_900,  'max' => 12_900],
                'ACCOMPANIMENT'         => ['min' => 14_900, 'max' => 21_900],
                'REGULAR_EVOLUTION'     => ['min' => 24_900, 'max' => 39_900],
                'CONTINUOUS_SEO'        => ['min' => 30_000, 'max' => 60_000],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $this->addSql(
            "INSERT INTO estimator_pricing_configuration
                (id, version, status, currency, configuration, created_by, published_by, created_at, updated_at, published_at)
             VALUES
                ('018f4a2b-0000-7000-8000-000000002026',
                 '2026-v1',
                 'published',
                 'EUR',
                 :config::jsonb,
                 NULL,
                 NULL,
                 now(),
                 now(),
                 now())",
            ['config' => $config],
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_pricing_created_at');
        $this->addSql('DROP INDEX idx_pricing_status');
        $this->addSql('DROP INDEX uniq_pricing_one_published');
        $this->addSql('DROP INDEX uniq_pricing_version');
        $this->addSql('DROP TABLE estimator_pricing_configuration');
    }
}
