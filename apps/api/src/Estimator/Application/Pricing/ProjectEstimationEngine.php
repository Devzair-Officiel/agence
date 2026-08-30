<?php

declare(strict_types=1);

namespace App\Estimator\Application\Pricing;

use App\Estimator\Domain\CareNeed;
use App\Estimator\Domain\EstimateAssumption;
use App\Estimator\Domain\EstimateLineItem;
use App\Estimator\Domain\EstimateRange;
use App\Estimator\Domain\EstimateResult;
use App\Estimator\Domain\LineItemCategory;
use App\Estimator\Domain\Money;
use App\Estimator\Domain\ProjectEstimateInput;
use App\Estimator\Domain\ProjectFeature;
use App\Estimator\Domain\ProjectObjective;
use App\Estimator\Domain\ProjectScale;
use App\Estimator\Domain\ProjectType;

/**
 * Moteur tarifaire autoritaire de l'Estimateur Devzair.
 *
 * Propriétés garanties :
 * - déterministe : même input + même catalog → même résultat ;
 * - pur : aucun I/O, HTTP, Doctrine, session, ni PII ;
 * - estimateRange = somme arrondie des oneOffItems uniquement ;
 * - recurringItems exclus du total initial.
 *
 * Règles de composition (V1) :
 * 1. SOCLE selon projectType, ajusté par scale (scale n'affecte que le socle).
 * 2. FEATURES additionnées au socle (une ligne par feature).
 * 3. CONTENT additionnés (une ligne par ContentNeed).
 * 4. VISIBILITY : SeoBasic inclus dans tous les socles — pas de ligne.
 * 5. estimateRange = arrondi conservateur de la somme one-off.
 * 6. RECURRING : un seul tier de maintenance (anti-doublon) + SEO continu orthogonal.
 * 7. ASSUMPTIONS : dédupliquées avant de construire EstimateResult.
 */
final class ProjectEstimationEngine
{
    public function __construct(
        private readonly PricingCatalogInterface $catalog,
    ) {}

    public function estimate(ProjectEstimateInput $input): EstimateResult
    {
        $assumptionCodes = [];
        $oneOffItems     = [];

        // 1. Résoudre le type recommandé et les montants de base
        [$recommendedType, $baseAmounts, $baseAssumptions] = $this->resolveBase($input);
        $assumptionCodes = array_merge($assumptionCodes, $baseAssumptions);

        // 2. Appliquer le scale uniquement au socle
        [$scaledMin, $scaledMax] = $this->scaleBase($baseAmounts, $input->scale);

        if ($input->scale === ProjectScale::Unknown) {
            $assumptionCodes[] = 'scope_to_confirm';
        }

        $oneOffItems[] = EstimateLineItem::create(
            'BASE',
            EstimateRange::create(Money::of($scaledMin, 'EUR'), Money::of($scaledMax, 'EUR')),
            LineItemCategory::OneOff,
        );

        // 3. Features (une ligne par feature, scale non appliqué aux features)
        $advancedCount = 0;
        foreach ($input->features as $feature) {
            $complexity = $this->catalog->featureComplexity($feature);

            if ($complexity === FeatureComplexity::Advanced) {
                ++$advancedCount;
            }

            if ($feature === ProjectFeature::ApiIntegration) {
                $assumptionCodes[] = 'external_integration_to_confirm';
            }

            $amounts       = $this->catalog->complexityAmounts($complexity);
            $oneOffItems[] = EstimateLineItem::create(
                'FEATURE_' . strtoupper($feature->value),
                EstimateRange::create(Money::of($amounts['min'], 'EUR'), Money::of($amounts['max'], 'EUR')),
                LineItemCategory::OneOff,
            );
        }

        if ($advancedCount >= $this->catalog->advancedFeaturesWarningThreshold()) {
            $assumptionCodes[] = 'advanced_scope_requires_confirmation';
        }

        // 4. Contenus
        foreach ($input->contentNeeds as $need) {
            $amounts       = $this->catalog->contentAmounts($need);
            $oneOffItems[] = EstimateLineItem::create(
                'CONTENT_' . strtoupper($need->value),
                EstimateRange::create(Money::of($amounts['min'], 'EUR'), Money::of($amounts['max'], 'EUR')),
                LineItemCategory::OneOff,
            );
        }

        // 5. Visibilité (SeoBasic = inclus dans tous les socles, retourne null → ignoré)
        foreach ($input->visibilityNeeds as $need) {
            $amounts = $this->catalog->visibilityAmounts($need);
            if ($amounts === null) {
                continue;
            }
            $oneOffItems[] = EstimateLineItem::create(
                'VISIBILITY_' . strtoupper($need->value),
                EstimateRange::create(Money::of($amounts['min'], 'EUR'), Money::of($amounts['max'], 'EUR')),
                LineItemCategory::OneOff,
            );
        }

        // 6. estimateRange = somme exacte arrondie — uniquement one-off
        $totalMin = 0;
        $totalMax = 0;
        foreach ($oneOffItems as $item) {
            $totalMin += $item->range->minimum->amountMinor;
            $totalMax += $item->range->maximum->amountMinor;
        }
        $estimateRange = EstimateRange::create(
            Money::of(RoundingPolicy::roundMinConservative($totalMin), 'EUR'),
            Money::of(RoundingPolicy::roundMaxConservative($totalMax), 'EUR'),
        );

        // 7. Récurrent (anti-doublon : un seul tier de maintenance)
        $recurringItems = $this->buildRecurringItems($input);

        // 8. Assumption Refonte
        if ($recommendedType === ProjectType::Refonte) {
            $assumptionCodes[] = 'redesign_existing_system_to_audit';
        }

        // 9. Déduplication et construction de la liste d'assumptions
        $assumptions = $this->deduplicateAssumptions($assumptionCodes);

        return new EstimateResult(
            recommendedProjectType: $recommendedType,
            estimateRange:          $estimateRange,
            oneOffItems:            $oneOffItems,
            recurringItems:         $recurringItems,
            assumptions:            $assumptions,
            pricingVersion:         $this->catalog->version(),
        );
    }

    /**
     * Résout le type de projet recommandé et les montants de base.
     *
     * Pour Unknown/Other : tente une inférence par objectifs.
     * Si l'inférence échoue : utilise la fourchette large + assumption humaine.
     *
     * @return array{0: ProjectType, 1: array{min: int, max: int}, 2: list<string>}
     */
    private function resolveBase(ProjectEstimateInput $input): array
    {
        $type = $input->projectType;

        if ($type !== ProjectType::Other && $type !== ProjectType::Unknown) {
            return [$type, $this->catalog->baseAmounts($type), []];
        }

        $inferred = $this->inferProjectType($input);
        if ($inferred !== null) {
            return [$inferred, $this->catalog->baseAmounts($inferred), []];
        }

        return [
            $type,
            $this->catalog->unknownFallbackAmounts(),
            ['unknown_project_requires_human_scoping'],
        ];
    }

    /**
     * Tente d'inférer un ProjectType depuis les objectifs déclarés.
     *
     * Règles déterministes V1 — deux niveaux de signal :
     *
     * FORT (implique une famille précise) :
     *   - SellOnline        → Ecommerce
     *   - DigitalizeProcess → BusinessApp
     *
     * FAIBLE (compatible vitrine, cède aux signaux forts) :
     *   - PresentBusiness | GenerateLeads | ImproveVisibility → VitrineSite
     *
     * Résolution :
     *   - 2+ familles fortes distinctes → conflit → null (human scoping requis).
     *   - 1 seule famille forte → retourner ce type (signaux faibles ignorés).
     *   - 0 famille forte + ≥1 signal faible → VitrineSite.
     *   - 0 signal → null.
     */
    private function inferProjectType(ProjectEstimateInput $input): ?ProjectType
    {
        $strongFamilies = [];
        foreach ($input->objectives as $objective) {
            $family = match ($objective) {
                ProjectObjective::SellOnline        => ProjectType::Ecommerce,
                ProjectObjective::DigitalizeProcess => ProjectType::BusinessApp,
                default                             => null,
            };
            if ($family !== null) {
                $strongFamilies[$family->value] = $family;
            }
        }

        if (count($strongFamilies) > 1) {
            return null;
        }

        if (count($strongFamilies) === 1) {
            return reset($strongFamilies);
        }

        foreach ($input->objectives as $objective) {
            if ($objective === ProjectObjective::PresentBusiness
                || $objective === ProjectObjective::GenerateLeads
                || $objective === ProjectObjective::ImproveVisibility) {
                return ProjectType::VitrineSite;
            }
        }

        return null;
    }

    /**
     * Applique le scale au socle uniquement (entiers, pas de float).
     *
     * @param array{min: int, max: int} $baseAmounts
     * @return array{0: int, 1: int}
     */
    private function scaleBase(array $baseAmounts, ProjectScale $scale): array
    {
        $percents = $this->catalog->scalePercents($scale);

        return [
            intdiv($baseAmounts['min'] * $percents['min'], 100),
            intdiv($baseAmounts['max'] * $percents['max'], 100),
        ];
    }

    /**
     * Construit les lignes récurrentes avec logique anti-doublon.
     *
     * Tier de maintenance (exclusif, on prend le plus haut applicable) :
     * FunctionalEvolution → REGULAR_EVOLUTION
     * ContentUpdates      → ACCOMPANIMENT
     * Support             → MAINTENANCE_FOLLOWUP
     * Maintenance         → ESSENTIAL_MAINTENANCE
     *
     * ContinuousSeo est orthogonal : ajouté séparément si sélectionné.
     *
     * @return list<EstimateLineItem>
     */
    private function buildRecurringItems(ProjectEstimateInput $input): array
    {
        $items    = [];
        $baseTier = null;

        if ($input->hasCareNeed(CareNeed::FunctionalEvolution)) {
            $baseTier = 'REGULAR_EVOLUTION';
        } elseif ($input->hasCareNeed(CareNeed::ContentUpdates)) {
            $baseTier = 'ACCOMPANIMENT';
        } elseif ($input->hasCareNeed(CareNeed::Support)) {
            $baseTier = 'MAINTENANCE_FOLLOWUP';
        } elseif ($input->hasCareNeed(CareNeed::Maintenance)) {
            $baseTier = 'ESSENTIAL_MAINTENANCE';
        }

        if ($baseTier !== null) {
            $amounts = $this->catalog->recurringAmounts($baseTier);
            $items[] = EstimateLineItem::create(
                'RECURRING_' . $baseTier,
                EstimateRange::create(Money::of($amounts['min'], 'EUR'), Money::of($amounts['max'], 'EUR')),
                LineItemCategory::Recurring,
            );
        }

        if ($input->hasCareNeed(CareNeed::ContinuousSeo)) {
            $amounts = $this->catalog->recurringAmounts('CONTINUOUS_SEO');
            $items[] = EstimateLineItem::create(
                'RECURRING_CONTINUOUS_SEO',
                EstimateRange::create(Money::of($amounts['min'], 'EUR'), Money::of($amounts['max'], 'EUR')),
                LineItemCategory::Recurring,
            );
        }

        return $items;
    }

    /**
     * @param list<string>        $codes
     * @return list<EstimateAssumption>
     */
    private function deduplicateAssumptions(array $codes): array
    {
        $result = [];
        $seen   = [];

        foreach ($codes as $code) {
            if (!isset($seen[$code])) {
                $seen[$code] = true;
                $result[]    = EstimateAssumption::fromCode($code);
            }
        }

        return $result;
    }
}
