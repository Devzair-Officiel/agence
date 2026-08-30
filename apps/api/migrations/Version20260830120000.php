<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * EST-6 — Table `estimator_lead` : leads qualifiés de l'estimateur.
 *
 * Chaque lead est créé lors d'un `POST /api/estimate/lead`.
 * L'estimation est recalculée côté Symfony à partir du snapshot questionnaire
 * et n'est jamais issue des prix envoyés par le navigateur.
 *
 * Colonnes JSONB :
 *   - `questionnaire_snapshot` : champs du questionnaire transmis (sans PII).
 *   - `estimate_snapshot`      : résultat produit par ProjectEstimationEngine
 *     au moment du lead (outcome, estimate, items, assumptions, pricing_version).
 *
 * `estimate_minimum_minor` et `estimate_maximum_minor` sont NULL quand
 * `outcome = human_scoping_required` (pas d'estimation chiffrable).
 *
 * Pas de FK vers une table de requêtes estimateur : les deux requêtes sont
 * distinctes et potentiellement séparées dans le temps.
 */
final class Version20260830120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'EST-6 — Crée la table estimator_lead (leads qualifiés de l\'estimateur, snapshot questionnaire + estimation recalculée).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE estimator_lead (
                id                       UUID          NOT NULL,
                name                     VARCHAR(120)  NOT NULL,
                email                    VARCHAR(254)  NOT NULL,
                phone                    VARCHAR(40)   DEFAULT NULL,
                company                  VARCHAR(160)  DEFAULT NULL,
                additional_feature_note  TEXT          DEFAULT NULL,
                project_type             VARCHAR(80)   NOT NULL,
                outcome                  VARCHAR(40)   NOT NULL,
                estimate_minimum_minor   INTEGER       DEFAULT NULL,
                estimate_maximum_minor   INTEGER       DEFAULT NULL,
                pricing_version          VARCHAR(40)   NOT NULL,
                questionnaire_snapshot   JSONB         NOT NULL,
                estimate_snapshot        JSONB         NOT NULL,
                request_id               VARCHAR(36)   NOT NULL,
                created_at               TIMESTAMPTZ   NOT NULL DEFAULT now(),
                PRIMARY KEY (id)
            )
        SQL);

        $this->addSql('CREATE INDEX idx_estimator_lead_created_at ON estimator_lead (created_at DESC)');
        $this->addSql('CREATE INDEX idx_estimator_lead_outcome    ON estimator_lead (outcome)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_estimator_lead_outcome');
        $this->addSql('DROP INDEX idx_estimator_lead_created_at');
        $this->addSql('DROP TABLE estimator_lead');
    }
}
