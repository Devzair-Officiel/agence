<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Application\Pricing;

use App\Estimator\Application\Pricing\DevzairPricingCatalogV1;
use App\Estimator\Application\Pricing\FeatureComplexity;
use App\Estimator\Domain\CareNeed;
use App\Estimator\Domain\ContentNeed;
use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use App\Estimator\Domain\ProjectFeature;
use App\Estimator\Domain\ProjectScale;
use App\Estimator\Domain\ProjectType;
use App\Estimator\Domain\VisibilityNeed;
use PHPUnit\Framework\TestCase;

/**
 * Ces valeurs sont des fixtures de test pour valider la structure de la grille.
 * Les montants reflètent la politique commerciale Devzair V1 (2026-v1).
 */
final class DevzairPricingCatalogV1Test extends TestCase
{
    private DevzairPricingCatalogV1 $catalog;

    protected function setUp(): void
    {
        $this->catalog = new DevzairPricingCatalogV1();
    }

    public function testVersionIs2026V1(): void
    {
        self::assertSame('2026-v1', $this->catalog->version()->value());
    }

    // ---- Base amounts ----

    #[\PHPUnit\Framework\Attributes\DataProvider('baseAmountsProvider')]
    public function testBaseAmountsForKnownTypes(ProjectType $type, int $expectedMin, int $expectedMax): void
    {
        $amounts = $this->catalog->baseAmounts($type);

        self::assertNotNull($amounts);
        self::assertSame($expectedMin, $amounts['min']);
        self::assertSame($expectedMax, $amounts['max']);
    }

    public static function baseAmountsProvider(): iterable
    {
        yield 'vitrinesite' => [ProjectType::VitrineSite, 90_000, 130_000];
        yield 'ecommerce'   => [ProjectType::Ecommerce,  170_000, 250_000];
        yield 'businessapp' => [ProjectType::BusinessApp, 250_000, 400_000];
        yield 'refonte'     => [ProjectType::Refonte,    70_000, 130_000];
    }

    public function testBaseAmountsReturnsNullForUnknown(): void
    {
        self::assertNull($this->catalog->baseAmounts(ProjectType::Unknown));
    }

    public function testBaseAmountsReturnsNullForOther(): void
    {
        self::assertNull($this->catalog->baseAmounts(ProjectType::Other));
    }

    public function testUnknownFallbackAmountsSpanFromVitrineToBusinessApp(): void
    {
        $fallback = $this->catalog->unknownFallbackAmounts();

        self::assertSame(90_000, $fallback['min']);
        self::assertSame(400_000, $fallback['max']);
    }

    // ---- Scale ----

    #[\PHPUnit\Framework\Attributes\DataProvider('scalePercentsProvider')]
    public function testScalePercents(ProjectScale $scale, int $minPct, int $maxPct): void
    {
        $percents = $this->catalog->scalePercents($scale);

        self::assertSame($minPct, $percents['min']);
        self::assertSame($maxPct, $percents['max']);
    }

    public static function scalePercentsProvider(): iterable
    {
        yield 'small'   => [ProjectScale::Small,   100, 100];
        yield 'medium'  => [ProjectScale::Medium,  115, 120];
        yield 'large'   => [ProjectScale::Large,   130, 145];
        yield 'unknown' => [ProjectScale::Unknown, 100, 100];
    }

    // ---- Feature complexity ----

    #[\PHPUnit\Framework\Attributes\DataProvider('featureComplexityProvider')]
    public function testFeatureComplexity(ProjectFeature $feature, FeatureComplexity $expected): void
    {
        self::assertSame($expected, $this->catalog->featureComplexity($feature));
    }

    public static function featureComplexityProvider(): iterable
    {
        yield 'ContactForm is Light'         => [ProjectFeature::ContactForm,        FeatureComplexity::Light];
        yield 'Multilingual is Light'        => [ProjectFeature::Multilingual,       FeatureComplexity::Light];
        yield 'ContentManagement is Light'   => [ProjectFeature::ContentManagement,  FeatureComplexity::Light];
        yield 'Payment is Medium'            => [ProjectFeature::Payment,            FeatureComplexity::Medium];
        yield 'Shipping is Medium'           => [ProjectFeature::Shipping,           FeatureComplexity::Medium];
        yield 'BookingForm is Medium'        => [ProjectFeature::BookingForm,        FeatureComplexity::Medium];
        yield 'CustomerAccounts is Medium'   => [ProjectFeature::CustomerAccounts,   FeatureComplexity::Medium];
        yield 'Notifications is Medium'      => [ProjectFeature::Notifications,      FeatureComplexity::Medium];
        yield 'ImportExport is Medium'       => [ProjectFeature::ImportExport,       FeatureComplexity::Medium];
        yield 'StockManagement is Medium'    => [ProjectFeature::StockManagement,    FeatureComplexity::Medium];
        yield 'Promotions is Medium'         => [ProjectFeature::Promotions,         FeatureComplexity::Medium];
        yield 'Authentication is Advanced'   => [ProjectFeature::Authentication,     FeatureComplexity::Advanced];
        yield 'RolesAndPermissions Advanced' => [ProjectFeature::RolesAndPermissions, FeatureComplexity::Advanced];
        yield 'DocumentGeneration Advanced'  => [ProjectFeature::DocumentGeneration, FeatureComplexity::Advanced];
        yield 'ApiIntegration is Advanced'   => [ProjectFeature::ApiIntegration,     FeatureComplexity::Advanced];
    }

    // ---- Complexity amounts ----

    public function testLightComplexityAmounts(): void
    {
        $amounts = $this->catalog->complexityAmounts(FeatureComplexity::Light);
        self::assertSame(10_000, $amounts['min']);
        self::assertSame(35_000, $amounts['max']);
    }

    public function testMediumComplexityAmounts(): void
    {
        $amounts = $this->catalog->complexityAmounts(FeatureComplexity::Medium);
        self::assertSame(35_000, $amounts['min']);
        self::assertSame(80_000, $amounts['max']);
    }

    public function testAdvancedComplexityAmounts(): void
    {
        $amounts = $this->catalog->complexityAmounts(FeatureComplexity::Advanced);
        self::assertSame(70_000, $amounts['min']);
        self::assertSame(180_000, $amounts['max']);
    }

    // ---- Content amounts ----

    #[\PHPUnit\Framework\Attributes\DataProvider('contentAmountsProvider')]
    public function testContentAmounts(ContentNeed $need, int $expectedMin, int $expectedMax): void
    {
        $amounts = $this->catalog->contentAmounts($need);

        self::assertSame($expectedMin, $amounts['min']);
        self::assertSame($expectedMax, $amounts['max']);
    }

    public static function contentAmountsProvider(): iterable
    {
        yield 'visual_identity_needed'  => [ContentNeed::VisualIdentityNeeded,  50_000, 100_000];
        yield 'visual_identity_partial' => [ContentNeed::VisualIdentityPartial, 25_000, 50_000];
        yield 'content_to_write'        => [ContentNeed::ContentToWrite,        40_000, 90_000];
        yield 'content_with_help'       => [ContentNeed::ContentWithHelp,       15_000, 40_000];
        yield 'photography_needed'      => [ContentNeed::PhotographyNeeded,     25_000, 60_000];
    }

    public function testVisualIdentityNeededIsMoreExpensiveThanPartial(): void
    {
        $full    = $this->catalog->contentAmounts(ContentNeed::VisualIdentityNeeded);
        $partial = $this->catalog->contentAmounts(ContentNeed::VisualIdentityPartial);

        self::assertGreaterThan($partial['min'], $full['min']);
        self::assertGreaterThan($partial['max'], $full['max']);
    }

    public function testContentToWriteIsMoreExpensiveThanWithHelp(): void
    {
        $full = $this->catalog->contentAmounts(ContentNeed::ContentToWrite);
        $help = $this->catalog->contentAmounts(ContentNeed::ContentWithHelp);

        self::assertGreaterThan($help['min'], $full['min']);
        self::assertGreaterThan($help['max'], $full['max']);
    }

    // ---- Visibility amounts ----

    public function testSeoBasicReturnsNull(): void
    {
        self::assertNull($this->catalog->visibilityAmounts(VisibilityNeed::SeoBasic));
    }

    public function testSeoAdvancedAmounts(): void
    {
        $amounts = $this->catalog->visibilityAmounts(VisibilityNeed::SeoAdvanced);
        self::assertNotNull($amounts);
        self::assertSame(30_000, $amounts['min']);
        self::assertSame(80_000, $amounts['max']);
    }

    public function testLocalVisibilityAmounts(): void
    {
        $amounts = $this->catalog->visibilityAmounts(VisibilityNeed::LocalVisibility);
        self::assertNotNull($amounts);
        self::assertSame(20_000, $amounts['min']);
        self::assertSame(50_000, $amounts['max']);
    }

    public function testEditorialStrategyAmounts(): void
    {
        $amounts = $this->catalog->visibilityAmounts(VisibilityNeed::EditorialStrategy);
        self::assertNotNull($amounts);
        self::assertSame(30_000, $amounts['min']);
        self::assertSame(70_000, $amounts['max']);
    }

    // ---- Recurring amounts ----

    #[\PHPUnit\Framework\Attributes\DataProvider('recurringAmountsProvider')]
    public function testRecurringAmounts(string $tier, int $expectedMin, int $expectedMax): void
    {
        $amounts = $this->catalog->recurringAmounts($tier);

        self::assertSame($expectedMin, $amounts['min']);
        self::assertSame($expectedMax, $amounts['max']);
    }

    public static function recurringAmountsProvider(): iterable
    {
        yield 'ESSENTIAL_MAINTENANCE' => ['ESSENTIAL_MAINTENANCE', 4_900,  6_900];
        yield 'MAINTENANCE_FOLLOWUP'  => ['MAINTENANCE_FOLLOWUP',  8_900,  12_900];
        yield 'ACCOMPANIMENT'         => ['ACCOMPANIMENT',         14_900, 21_900];
        yield 'REGULAR_EVOLUTION'     => ['REGULAR_EVOLUTION',     24_900, 39_900];
        yield 'CONTINUOUS_SEO'        => ['CONTINUOUS_SEO',        30_000, 60_000];
    }

    public function testRecurringTiersAreOrderedAscending(): void
    {
        $essential = $this->catalog->recurringAmounts('ESSENTIAL_MAINTENANCE');
        $followup  = $this->catalog->recurringAmounts('MAINTENANCE_FOLLOWUP');
        $accomp    = $this->catalog->recurringAmounts('ACCOMPANIMENT');
        $evolution = $this->catalog->recurringAmounts('REGULAR_EVOLUTION');

        self::assertLessThan($followup['min'], $essential['min']);
        self::assertLessThan($accomp['min'], $followup['min']);
        self::assertLessThan($evolution['min'], $accomp['min']);
    }

    public function testUnknownRecurringTierThrows(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        $this->catalog->recurringAmounts('UNKNOWN_TIER');
    }

    public function testAdvancedFeaturesThresholdIsTwo(): void
    {
        self::assertSame(2, $this->catalog->advancedFeaturesWarningThreshold());
    }
}
