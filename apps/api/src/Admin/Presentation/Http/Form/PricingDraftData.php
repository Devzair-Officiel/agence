<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http\Form;

use App\Estimator\Application\Pricing\PricingCatalogDefinition;
use App\Estimator\Domain\Pricing\PricingConfiguration;
use Symfony\Component\HttpFoundation\Request;

/**
 * Objet de transfert entre les formulaires admin et la couche application.
 *
 * Invariants de conversion :
 * - Les montants (base_amounts, complexity_amounts, content_amounts,
 *   visibility_amounts, recurring_amounts) sont en EUROS dans ce VO
 *   (entiers, V1 — pas de centimes décimaux) et en centimes dans
 *   PricingCatalogDefinition.
 * - Les scale_percents sont des pourcentages entiers (100 = neutre) —
 *   pas de conversion.
 * - visibility_amounts[seo_basic] est TOUJOURS null : invariant SEO non exposé
 *   dans le formulaire.
 */
final class PricingDraftData
{
    public string $version  = '';
    public string $currency = 'EUR';

    /** @var array<string, array{min: int, max: int}> euros */
    public array $baseAmounts = [];

    /** @var array<string, array{min: int, max: int}> percent points */
    public array $scalePercents = [];

    /** @var array<string, string> */
    public array $featureComplexity = [];

    /** @var array<string, array{min: int, max: int}> euros */
    public array $complexityAmounts = [];

    /** @var array<string, array{min: int, max: int}> euros */
    public array $contentAmounts = [];

    /** @var array<string, array{min: int, max: int}|null> euros (null = inclus) */
    public array $visibilityAmounts = [];

    /** @var array<string, array{min: int, max: int}> euros */
    public array $recurringAmounts = [];

    /**
     * Hydrate depuis une PricingConfiguration persistée (centimes → euros).
     */
    public static function fromConfiguration(PricingConfiguration $config): self
    {
        $def = PricingCatalogDefinition::fromArray(
            $config->version(),
            $config->currency(),
            $config->configuration(),
        );

        return self::fromDefinition($def);
    }

    /**
     * Hydrate depuis une PricingCatalogDefinition (centimes → euros).
     */
    public static function fromDefinition(PricingCatalogDefinition $def): self
    {
        $data                   = new self();
        $data->version          = $def->version;
        $data->currency         = $def->currency;
        $data->baseAmounts      = self::centsToEuros($def->baseAmounts);
        $data->scalePercents    = $def->scalePercents;
        $data->featureComplexity = $def->featureComplexity;
        $data->complexityAmounts = self::centsToEuros($def->complexityAmounts);
        $data->contentAmounts   = self::centsToEuros($def->contentAmounts);
        $data->visibilityAmounts = self::centsToEurosNullable($def->visibilityAmounts);
        $data->recurringAmounts = self::centsToEuros($def->recurringAmounts);

        return $data;
    }

    /**
     * Hydrate depuis une requête HTTP (euros depuis le formulaire → euros dans ce VO).
     *
     * La version est immuable après création : elle vient de l'URL, pas du formulaire.
     */
    public static function hydrate(Request $request, string $version): self
    {
        $all = $request->request->all();

        $data          = new self();
        $data->version = $version;
        $data->currency = 'EUR';

        $data->baseAmounts       = self::parseRangeMap($all['base_amounts'] ?? []);
        $data->scalePercents     = self::parseRangeMap($all['scale_percents'] ?? []);
        $data->featureComplexity = self::parseStringMap($all['feature_complexity'] ?? []);
        $data->complexityAmounts = self::parseRangeMap($all['complexity_amounts'] ?? []);
        $data->contentAmounts    = self::parseRangeMap($all['content_amounts'] ?? []);
        $data->visibilityAmounts = self::parseNullableRangeMap($all['visibility_amounts'] ?? []);
        $data->recurringAmounts  = self::parseRangeMap($all['recurring_amounts'] ?? []);

        // seo_basic MUST be null (invariant — not editable via form)
        $data->visibilityAmounts['seo_basic'] = null;

        return $data;
    }

    /**
     * Convertit ce VO en PricingCatalogDefinition (euros → centimes).
     */
    public function toDefinition(): PricingCatalogDefinition
    {
        $visibilityInCents                = self::eurosToCentsNullable($this->visibilityAmounts);
        $visibilityInCents['seo_basic']   = null;

        return new PricingCatalogDefinition(
            version:           $this->version,
            currency:          $this->currency,
            baseAmounts:       self::eurosToCents($this->baseAmounts),
            scalePercents:     $this->scalePercents,
            featureComplexity: $this->featureComplexity,
            complexityAmounts: self::eurosToCents($this->complexityAmounts),
            contentAmounts:    self::eurosToCents($this->contentAmounts),
            visibilityAmounts: $visibilityInCents,
            recurringAmounts:  self::eurosToCents($this->recurringAmounts),
        );
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * @param array<string, mixed> $map
     * @return array<string, array{min: int, max: int}>
     */
    private static function parseRangeMap(array $map): array
    {
        $result = [];
        foreach ($map as $key => $value) {
            if (!is_array($value)) {
                continue;
            }
            $result[(string) $key] = [
                'min' => (int) ($value['min'] ?? 0),
                'max' => (int) ($value['max'] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $map
     * @return array<string, array{min: int, max: int}|null>
     */
    private static function parseNullableRangeMap(array $map): array
    {
        $result = [];
        foreach ($map as $key => $value) {
            if ($value === null || $value === '') {
                $result[(string) $key] = null;
                continue;
            }
            if (!is_array($value)) {
                continue;
            }
            $result[(string) $key] = [
                'min' => (int) ($value['min'] ?? 0),
                'max' => (int) ($value['max'] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $map
     * @return array<string, string>
     */
    private static function parseStringMap(array $map): array
    {
        $result = [];
        foreach ($map as $key => $value) {
            $result[(string) $key] = (string) $value;
        }

        return $result;
    }

    /**
     * @param array<string, array{min: int, max: int}> $map amounts in cents
     * @return array<string, array{min: int, max: int}> amounts in euros
     */
    private static function centsToEuros(array $map): array
    {
        $result = [];
        foreach ($map as $key => $range) {
            $result[$key] = [
                'min' => (int) round($range['min'] / 100),
                'max' => (int) round($range['max'] / 100),
            ];
        }

        return $result;
    }

    /**
     * @param array<string, array{min: int, max: int}|null> $map amounts in cents
     * @return array<string, array{min: int, max: int}|null> amounts in euros
     */
    private static function centsToEurosNullable(array $map): array
    {
        $result = [];
        foreach ($map as $key => $range) {
            if ($range === null) {
                $result[$key] = null;
                continue;
            }
            $result[$key] = [
                'min' => (int) round($range['min'] / 100),
                'max' => (int) round($range['max'] / 100),
            ];
        }

        return $result;
    }

    /**
     * @param array<string, array{min: int, max: int}> $map amounts in euros
     * @return array<string, array{min: int, max: int}> amounts in cents
     */
    private static function eurosToCents(array $map): array
    {
        $result = [];
        foreach ($map as $key => $range) {
            $result[$key] = [
                'min' => $range['min'] * 100,
                'max' => $range['max'] * 100,
            ];
        }

        return $result;
    }

    /**
     * @param array<string, array{min: int, max: int}|null> $map amounts in euros
     * @return array<string, array{min: int, max: int}|null> amounts in cents
     */
    private static function eurosToCentsNullable(array $map): array
    {
        $result = [];
        foreach ($map as $key => $range) {
            if ($range === null) {
                $result[$key] = null;
                continue;
            }
            $result[$key] = [
                'min' => $range['min'] * 100,
                'max' => $range['max'] * 100,
            ];
        }

        return $result;
    }
}
