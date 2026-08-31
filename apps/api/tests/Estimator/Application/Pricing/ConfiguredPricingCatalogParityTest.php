<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Application\Pricing;

use App\Estimator\Application\Pricing\ConfiguredPricingCatalog;
use App\Estimator\Application\Pricing\DevzairPricingCatalogV1;
use App\Estimator\Application\Pricing\FeatureComplexity;
use App\Estimator\Application\Pricing\PricingCatalogDefinition;
use App\Estimator\Domain\ContentNeed;
use App\Estimator\Domain\ProjectFeature;
use App\Estimator\Domain\ProjectScale;
use App\Estimator\Domain\ProjectType;
use App\Estimator\Domain\VisibilityNeed;
use PHPUnit\Framework\TestCase;

/**
 * Garantit que ConfiguredPricingCatalog hydraté depuis la migration 2026-v1
 * retourne exactement les mêmes valeurs que DevzairPricingCatalogV1.
 *
 * Ce test de parité est le garde-fou contre toute divergence entre la grille
 * hardcodée (référence) et la grille stockée en base (production).
 */
final class ConfiguredPricingCatalogParityTest extends TestCase
{
    private DevzairPricingCatalogV1 $v1;
    private ConfiguredPricingCatalog $configured;

    protected function setUp(): void
    {
        $this->v1 = new DevzairPricingCatalogV1();

        $definition = PricingCatalogDefinition::fromArray('2026-v1', 'EUR', [
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
        ]);

        $this->configured = new ConfiguredPricingCatalog($definition);
    }

    public function testVersionParity(): void
    {
        self::assertSame(
            $this->v1->version()->value(),
            $this->configured->version()->value(),
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('projectTypeProvider')]
    public function testBaseAmountsParity(ProjectType $type): void
    {
        self::assertSame(
            $this->v1->baseAmounts($type),
            $this->configured->baseAmounts($type),
            "base_amounts mismatch for {$type->value}",
        );
    }

    public static function projectTypeProvider(): iterable
    {
        foreach (ProjectType::cases() as $type) {
            yield $type->value => [$type];
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('projectScaleProvider')]
    public function testScalePercentsParity(ProjectScale $scale): void
    {
        self::assertSame(
            $this->v1->scalePercents($scale),
            $this->configured->scalePercents($scale),
            "scale_percents mismatch for {$scale->value}",
        );
    }

    public static function projectScaleProvider(): iterable
    {
        foreach (ProjectScale::cases() as $scale) {
            yield $scale->value => [$scale];
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('projectFeatureProvider')]
    public function testFeatureComplexityParity(ProjectFeature $feature): void
    {
        self::assertSame(
            $this->v1->featureComplexity($feature),
            $this->configured->featureComplexity($feature),
            "feature_complexity mismatch for {$feature->value}",
        );
    }

    public static function projectFeatureProvider(): iterable
    {
        foreach (ProjectFeature::cases() as $feature) {
            yield $feature->value => [$feature];
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('complexityProvider')]
    public function testComplexityAmountsParity(FeatureComplexity $complexity): void
    {
        self::assertSame(
            $this->v1->complexityAmounts($complexity),
            $this->configured->complexityAmounts($complexity),
            "complexity_amounts mismatch for {$complexity->value}",
        );
    }

    public static function complexityProvider(): iterable
    {
        foreach (FeatureComplexity::cases() as $c) {
            yield $c->value => [$c];
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('contentNeedProvider')]
    public function testContentAmountsParity(ContentNeed $need): void
    {
        self::assertSame(
            $this->v1->contentAmounts($need),
            $this->configured->contentAmounts($need),
            "content_amounts mismatch for {$need->value}",
        );
    }

    public static function contentNeedProvider(): iterable
    {
        foreach (ContentNeed::cases() as $need) {
            yield $need->value => [$need];
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('visibilityNeedProvider')]
    public function testVisibilityAmountsParity(VisibilityNeed $need): void
    {
        self::assertSame(
            $this->v1->visibilityAmounts($need),
            $this->configured->visibilityAmounts($need),
            "visibility_amounts mismatch for {$need->value}",
        );
    }

    public static function visibilityNeedProvider(): iterable
    {
        foreach (VisibilityNeed::cases() as $need) {
            yield $need->value => [$need];
        }
    }

    public function testRecurringAmountsParity(): void
    {
        $tiers = ['ESSENTIAL_MAINTENANCE', 'MAINTENANCE_FOLLOWUP', 'ACCOMPANIMENT', 'REGULAR_EVOLUTION', 'CONTINUOUS_SEO'];

        foreach ($tiers as $tier) {
            self::assertSame(
                $this->v1->recurringAmounts($tier),
                $this->configured->recurringAmounts($tier),
                "recurring_amounts mismatch for {$tier}",
            );
        }
    }

    public function testUnknownFallbackAmountsParity(): void
    {
        self::assertSame(
            $this->v1->unknownFallbackAmounts(),
            $this->configured->unknownFallbackAmounts(),
        );
    }
}
