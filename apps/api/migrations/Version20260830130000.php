<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * EST-7 — Table `estimator_partnership_proposal` : propositions de partenariat.
 *
 * Créée lorsqu'un prospect soumet POST /api/estimate/partnership.
 *
 * Règles métier garanties par le schéma :
 * - `status` démarre à `pending_review` ; seule une action humaine change cela.
 * - `estimate_minimum_minor` / `estimate_maximum_minor` / `currency` sont NULL
 *   quand outcome = human_scoping_required (aucun fallback commercial stocké).
 * - `lead_request_id` corrèle optionnellement avec un lead EST-6 sans FK formelle
 *   (V1 — relation légère pour éviter des cascades commercialement risquées).
 *
 * Colonnes JSONB :
 *   - `questionnaire_snapshot` : champs du questionnaire (sans PII).
 *   - `estimate_snapshot`      : résultat de ProjectEstimationEngine au moment
 *     de la soumission (jamais les prix du navigateur).
 */
final class Version20260830130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "EST-7 — Crée la table estimator_partnership_proposal (propositions de partenariat, statut pending_review, estimation recalculée autoritairement).";
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE estimator_partnership_proposal (
                id                       UUID          NOT NULL,
                lead_request_id          VARCHAR(36)   DEFAULT NULL,
                name                     VARCHAR(120)  NOT NULL,
                email                    VARCHAR(254)  NOT NULL,
                phone                    VARCHAR(40)   DEFAULT NULL,
                company                  VARCHAR(160)  DEFAULT NULL,
                project_type             VARCHAR(80)   NOT NULL,
                pricing_version          VARCHAR(40)   NOT NULL,
                estimate_minimum_minor   INTEGER       DEFAULT NULL,
                estimate_maximum_minor   INTEGER       DEFAULT NULL,
                currency                 VARCHAR(3)    DEFAULT NULL,
                partnership_type         VARCHAR(80)   NOT NULL,
                project_stage            VARCHAR(80)   NOT NULL,
                generates_revenue        VARCHAR(40)   DEFAULT NULL,
                proposal_text            TEXT          NOT NULL,
                relevance_text           TEXT          DEFAULT NULL,
                questionnaire_snapshot   JSONB         NOT NULL,
                estimate_snapshot        JSONB         NOT NULL,
                request_id               VARCHAR(36)   NOT NULL,
                status                   VARCHAR(40)   NOT NULL DEFAULT 'pending_review',
                created_at               TIMESTAMPTZ   NOT NULL DEFAULT now(),
                PRIMARY KEY (id)
            )
        SQL);

        $this->addSql('CREATE INDEX idx_estimator_partnership_created_at ON estimator_partnership_proposal (created_at DESC)');
        $this->addSql('CREATE INDEX idx_estimator_partnership_status     ON estimator_partnership_proposal (status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_estimator_partnership_status');
        $this->addSql('DROP INDEX idx_estimator_partnership_created_at');
        $this->addSql('DROP TABLE estimator_partnership_proposal');
    }
}
