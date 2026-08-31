<?php

declare(strict_types=1);

namespace App\Estimator\Application\Pricing;

use App\Estimator\Domain\ContentNeed;
use App\Estimator\Domain\ProjectFeature;
use App\Estimator\Domain\ProjectScale;
use App\Estimator\Domain\ProjectType;
use App\Estimator\Domain\VisibilityNeed;

/**
 * Valide l'exhaustivité et la cohérence d'une PricingCatalogDefinition
 * avant sa création ou publication.
 *
 * Règles :
 * - Tous les ProjectType administrables (non Other/Unknown) ont un base_amount.
 * - Tous les ProjectScale ont un scale_percent.
 * - Toutes les ProjectFeature ont une feature_complexity connue.
 * - Tous les niveaux de complexité ont un complexity_amount.
 * - Tous les ContentNeed ont un content_amount.
 * - Tous les VisibilityNeed ont une entrée dans visibility_amounts (null = inclus).
 * - seo_basic DOIT être null (invariant SEO).
 * - Tous les recurring tiers requis ont un recurring_amount.
 * - Toutes les ranges : min >= 0, max >= 0, min <= max.
 * - currency = EUR (V1).
 *
 * @return list<string> liste des erreurs (vide si valide)
 */
final class PricingConfigurationValidator
{
    private const ADMINISTRABLE_TYPES = [
        ProjectType::VitrineSite->value,
        ProjectType::Ecommerce->value,
        ProjectType::BusinessApp->value,
        ProjectType::Refonte->value,
    ];

    private const REQUIRED_RECURRING_TIERS = [
        'ESSENTIAL_MAINTENANCE',
        'MAINTENANCE_FOLLOWUP',
        'ACCOMPANIMENT',
        'REGULAR_EVOLUTION',
        'CONTINUOUS_SEO',
    ];

    /**
     * @return list<string>
     */
    public function validate(PricingCatalogDefinition $def): array
    {
        $errors = [];

        // 1. currency
        if ($def->currency !== 'EUR') {
            $errors[] = sprintf('currency : seul "EUR" est supporté en V1, reçu "%s".', $def->currency);
        }

        // 2. base_amounts : types administrables
        foreach (self::ADMINISTRABLE_TYPES as $typeValue) {
            if (!isset($def->baseAmounts[$typeValue])) {
                $errors[] = sprintf('base_amounts[%s] manquant.', $typeValue);
            }
        }
        foreach ($def->baseAmounts as $key => $range) {
            $rangeErrors = $this->validateRange($range, "base_amounts[$key]");
            foreach ($rangeErrors as $e) {
                $errors[] = $e;
            }
        }

        // 3. scale_percents : tous les ProjectScale
        foreach (ProjectScale::cases() as $scale) {
            if (!isset($def->scalePercents[$scale->value])) {
                $errors[] = sprintf('scale_percents[%s] manquant.', $scale->value);
            }
        }
        foreach ($def->scalePercents as $key => $range) {
            if (!is_array($range) || !isset($range['min'], $range['max'])) {
                $errors[] = sprintf('scale_percents[%s] : format invalide.', $key);
                continue;
            }
            if ($range['min'] < 0) {
                $errors[] = sprintf('scale_percents[%s].min doit être >= 0.', $key);
            }
            if ($range['max'] < 0) {
                $errors[] = sprintf('scale_percents[%s].max doit être >= 0.', $key);
            }
            if ($range['min'] > $range['max']) {
                $errors[] = sprintf('scale_percents[%s] : min > max (%d > %d).', $key, $range['min'], $range['max']);
            }
        }

        // 4. feature_complexity : toutes les ProjectFeature
        $validComplexities = array_map(static fn(FeatureComplexity $c) => $c->value, FeatureComplexity::cases());
        foreach (ProjectFeature::cases() as $feature) {
            $complexity = $def->featureComplexity[$feature->value] ?? null;
            if ($complexity === null) {
                $errors[] = sprintf('feature_complexity[%s] manquant.', $feature->value);
            } elseif (!in_array($complexity, $validComplexities, true)) {
                $errors[] = sprintf('feature_complexity[%s] : valeur "%s" inconnue.', $feature->value, $complexity);
            }
        }

        // 5. complexity_amounts : tous les FeatureComplexity
        foreach (FeatureComplexity::cases() as $complexity) {
            if (!isset($def->complexityAmounts[$complexity->value])) {
                $errors[] = sprintf('complexity_amounts[%s] manquant.', $complexity->value);
            } else {
                foreach ($this->validateRange($def->complexityAmounts[$complexity->value], "complexity_amounts[{$complexity->value}]") as $e) {
                    $errors[] = $e;
                }
            }
        }

        // 6. content_amounts : tous les ContentNeed
        foreach (ContentNeed::cases() as $need) {
            if (!isset($def->contentAmounts[$need->value])) {
                $errors[] = sprintf('content_amounts[%s] manquant.', $need->value);
            } else {
                foreach ($this->validateRange($def->contentAmounts[$need->value], "content_amounts[{$need->value}]") as $e) {
                    $errors[] = $e;
                }
            }
        }

        // 7. visibility_amounts : tous les VisibilityNeed + invariant seo_basic = null
        foreach (VisibilityNeed::cases() as $need) {
            if (!array_key_exists($need->value, $def->visibilityAmounts)) {
                $errors[] = sprintf('visibility_amounts[%s] manquant.', $need->value);
                continue;
            }
            $visAmount = $def->visibilityAmounts[$need->value];
            if ($need === VisibilityNeed::SeoBasic) {
                if ($visAmount !== null) {
                    $errors[] = 'visibility_amounts[seo_basic] doit être null (SEO de base inclus dans tous les socles).';
                }
            } elseif ($visAmount !== null) {
                foreach ($this->validateRange($visAmount, "visibility_amounts[{$need->value}]") as $e) {
                    $errors[] = $e;
                }
            }
        }

        // 8. recurring_amounts : tiers requis
        foreach (self::REQUIRED_RECURRING_TIERS as $tier) {
            if (!isset($def->recurringAmounts[$tier])) {
                $errors[] = sprintf('recurring_amounts[%s] manquant.', $tier);
            } else {
                foreach ($this->validateRange($def->recurringAmounts[$tier], "recurring_amounts[$tier]") as $e) {
                    $errors[] = $e;
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<string, int> $range
     * @return list<string>
     */
    private function validateRange(array $range, string $label): array
    {
        $errors = [];

        if (!isset($range['min'], $range['max'])) {
            return [sprintf('%s : format invalide (attendu {"min": int, "max": int}).', $label)];
        }

        if ($range['min'] < 0) {
            $errors[] = sprintf('%s.min doit être >= 0 (reçu %d).', $label, $range['min']);
        }
        if ($range['max'] < 0) {
            $errors[] = sprintf('%s.max doit être >= 0 (reçu %d).', $label, $range['max']);
        }
        if ($range['min'] > $range['max']) {
            $errors[] = sprintf('%s : min (%d) > max (%d) — fourchette inversée.', $label, $range['min'], $range['max']);
        }

        return $errors;
    }
}
