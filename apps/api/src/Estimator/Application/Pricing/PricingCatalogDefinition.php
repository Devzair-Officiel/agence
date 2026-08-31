<?php

declare(strict_types=1);

namespace App\Estimator\Application\Pricing;

/**
 * Snapshot typé et validé d'une configuration tarifaire persistée.
 *
 * Toutes les données sont en minor units (centimes EUR).
 * Ce VO est immuable ; il ne connaît ni Doctrine ni HTTP.
 *
 * Structure JSON attendue dans `estimator_pricing_configuration.configuration` :
 * {
 *   "base_amounts":       { "<project_type>":   {"min": int, "max": int} },
 *   "scale_percents":     { "<project_scale>":  {"min": int, "max": int} },
 *   "feature_complexity": { "<feature>":        "light|medium|advanced"  },
 *   "complexity_amounts": { "light|medium|advanced": {"min": int, "max": int} },
 *   "content_amounts":    { "<content_need>":   {"min": int, "max": int} },
 *   "visibility_amounts": { "<visibility_need>": {"min": int, "max": int} | null },
 *   "recurring_amounts":  { "<tier_code>":      {"min": int, "max": int} }
 * }
 */
final readonly class PricingCatalogDefinition
{
    /**
     * @param array<string, array{min: int, max: int}>      $baseAmounts
     * @param array<string, array{min: int, max: int}>      $scalePercents
     * @param array<string, string>                         $featureComplexity
     * @param array<string, array{min: int, max: int}>      $complexityAmounts
     * @param array<string, array{min: int, max: int}>      $contentAmounts
     * @param array<string, array{min: int, max: int}|null> $visibilityAmounts
     * @param array<string, array{min: int, max: int}>      $recurringAmounts
     */
    public function __construct(
        public readonly string $version,
        public readonly string $currency,
        public readonly array  $baseAmounts,
        public readonly array  $scalePercents,
        public readonly array  $featureComplexity,
        public readonly array  $complexityAmounts,
        public readonly array  $contentAmounts,
        public readonly array  $visibilityAmounts,
        public readonly array  $recurringAmounts,
    ) {}

    /**
     * Hydrate depuis le tableau JSON stocké en base.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(string $version, string $currency, array $data): self
    {
        return new self(
            version:           $version,
            currency:          $currency,
            baseAmounts:       self::assertRangeMap($data['base_amounts'] ?? [], 'base_amounts'),
            scalePercents:     self::assertRangeMap($data['scale_percents'] ?? [], 'scale_percents'),
            featureComplexity: self::assertStringMap($data['feature_complexity'] ?? [], 'feature_complexity'),
            complexityAmounts: self::assertRangeMap($data['complexity_amounts'] ?? [], 'complexity_amounts'),
            contentAmounts:    self::assertRangeMap($data['content_amounts'] ?? [], 'content_amounts'),
            visibilityAmounts: self::assertNullableRangeMap($data['visibility_amounts'] ?? [], 'visibility_amounts'),
            recurringAmounts:  self::assertRangeMap($data['recurring_amounts'] ?? [], 'recurring_amounts'),
        );
    }

    /**
     * Sérialise vers le tableau JSON à stocker en base.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'base_amounts'       => $this->baseAmounts,
            'scale_percents'     => $this->scalePercents,
            'feature_complexity' => $this->featureComplexity,
            'complexity_amounts' => $this->complexityAmounts,
            'content_amounts'    => $this->contentAmounts,
            'visibility_amounts' => $this->visibilityAmounts,
            'recurring_amounts'  => $this->recurringAmounts,
        ];
    }

    // ─── Helpers d'hydratation ───────────────────────────────────────────────

    /**
     * @param array<string, mixed> $map
     * @return array<string, array{min: int, max: int}>
     */
    private static function assertRangeMap(array $map, string $field): array
    {
        $result = [];
        foreach ($map as $key => $value) {
            if (!is_array($value) || !isset($value['min'], $value['max'])) {
                throw new \InvalidArgumentException(sprintf(
                    'Configuration "%s[%s]" : attendu {"min": int, "max": int}, reçu : %s',
                    $field, $key, json_encode($value),
                ));
            }
            $result[(string) $key] = ['min' => (int) $value['min'], 'max' => (int) $value['max']];
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $map
     * @return array<string, array{min: int, max: int}|null>
     */
    private static function assertNullableRangeMap(array $map, string $field): array
    {
        $result = [];
        foreach ($map as $key => $value) {
            if ($value === null) {
                $result[(string) $key] = null;
                continue;
            }
            if (!is_array($value) || !isset($value['min'], $value['max'])) {
                throw new \InvalidArgumentException(sprintf(
                    'Configuration "%s[%s]" : attendu {"min": int, "max": int} ou null, reçu : %s',
                    $field, $key, json_encode($value),
                ));
            }
            $result[(string) $key] = ['min' => (int) $value['min'], 'max' => (int) $value['max']];
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $map
     * @return array<string, string>
     */
    private static function assertStringMap(array $map, string $field): array
    {
        $result = [];
        foreach ($map as $key => $value) {
            if (!is_string($value)) {
                throw new \InvalidArgumentException(sprintf(
                    'Configuration "%s[%s]" : attendu string, reçu : %s',
                    $field, $key, get_debug_type($value),
                ));
            }
            $result[(string) $key] = $value;
        }

        return $result;
    }
}
