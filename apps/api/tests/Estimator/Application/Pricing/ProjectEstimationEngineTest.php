<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Application\Pricing;

use App\Estimator\Application\Pricing\DevzairPricingCatalogV1;
use App\Estimator\Application\Pricing\ProjectEstimationEngine;
use App\Estimator\Domain\CareNeed;
use App\Estimator\Domain\ContentNeed;
use App\Estimator\Domain\ProjectFeature;
use App\Estimator\Domain\ProjectObjective;
use App\Estimator\Domain\ProjectScale;
use App\Estimator\Domain\ProjectType;
use App\Estimator\Domain\VisibilityNeed;
use App\Tests\Estimator\Support\EstimateInputBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Ces valeurs sont des fixtures de test et ne représentent pas les tarifs Devzair.
 * Les assertions vérifient les règles de composition du moteur, pas les montants commerciaux.
 */
final class ProjectEstimationEngineTest extends TestCase
{
    private ProjectEstimationEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new ProjectEstimationEngine(new DevzairPricingCatalogV1());
    }

    // ====================================================================
    // SOCLES DE BASE
    // ====================================================================

    public function testVitrineSmallBaseRange(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->build()
        );

        // BASE: 90000–130000, arrondi 50€ = inchangé
        self::assertSame(90_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(130_000, $result->estimateRange->maximum->amountMinor);
        self::assertSame(ProjectType::VitrineSite, $result->recommendedProjectType);
        self::assertCount(1, $result->oneOffItems); // BASE seul
    }

    public function testEcommerceSmallBaseRange(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Ecommerce)
                ->withScale(ProjectScale::Small)
                ->build()
        );

        self::assertSame(170_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(250_000, $result->estimateRange->maximum->amountMinor);
    }

    public function testBusinessAppSmallBaseRange(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::BusinessApp)
                ->withScale(ProjectScale::Small)
                ->build()
        );

        self::assertSame(250_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(400_000, $result->estimateRange->maximum->amountMinor);
    }

    public function testRefonteSmallBaseRange(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Refonte)
                ->withScale(ProjectScale::Small)
                ->build()
        );

        self::assertSame(70_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(130_000, $result->estimateRange->maximum->amountMinor);
    }

    // ====================================================================
    // SCALE
    // ====================================================================

    public function testMediumScaleAdjustsBaseSocleOnly(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Medium)
                ->build()
        );

        // BASE: 90000 * 115/100 = 103500, 130000 * 120/100 = 156000
        // roundMin(103500) = floor(103500/5000)*5000 = 20*5000 = 100000
        // roundMax(156000) = ceil(156000/5000)*5000 = 32*5000 = 160000
        self::assertSame(100_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(160_000, $result->estimateRange->maximum->amountMinor);

        // BASE line item stores exact scaled values
        $baseItem = $result->oneOffItems[0];
        self::assertSame('BASE', $baseItem->code);
        self::assertSame(103_500, $baseItem->range->minimum->amountMinor);
        self::assertSame(156_000, $baseItem->range->maximum->amountMinor);
    }

    public function testLargeScaleAdjustsBaseSocleOnly(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Large)
                ->build()
        );

        // BASE: 90000*130/100=117000, 130000*145/100=188500
        // roundMin(117000) = 115000, roundMax(188500) = 190000
        self::assertSame(115_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(190_000, $result->estimateRange->maximum->amountMinor);
    }

    public function testUnknownScaleAddsAssumptionAndLeavesBaseUnchanged(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Unknown)
                ->build()
        );

        // Scale unknown = 100/100 → base unchanged
        self::assertSame(90_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(130_000, $result->estimateRange->maximum->amountMinor);

        $codes = array_map(static fn($a) => $a->code(), $result->assumptions);
        self::assertContains('scope_to_confirm', $codes);
    }

    // ====================================================================
    // FEATURES
    // ====================================================================

    public function testLightFeatureAddedToTotal(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->withFeatures([ProjectFeature::ContactForm])
                ->build()
        );

        // BASE: 90000+130000, ContactForm Light: +10000+35000
        // Total exact: min=100000, max=165000 → rounded: 100000, 165000
        self::assertSame(100_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(165_000, $result->estimateRange->maximum->amountMinor);
        self::assertCount(2, $result->oneOffItems);
        self::assertSame('FEATURE_CONTACT_FORM', $result->oneOffItems[1]->code);
    }

    public function testMediumFeatureAdded(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->withFeatures([ProjectFeature::Payment])
                ->build()
        );

        // BASE 90000+35000=125000 min, 130000+80000=210000 max → rounded: 125000, 210000
        self::assertSame(125_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(210_000, $result->estimateRange->maximum->amountMinor);
        self::assertSame('FEATURE_PAYMENT', $result->oneOffItems[1]->code);
    }

    public function testAdvancedFeatureAdded(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->withFeatures([ProjectFeature::Authentication])
                ->build()
        );

        // BASE 90000+70000=160000 min, 130000+180000=310000 max
        self::assertSame(160_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(310_000, $result->estimateRange->maximum->amountMinor);
    }

    public function testMultipleFeaturesAccumulate(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->withFeatures([ProjectFeature::ContactForm, ProjectFeature::Payment, ProjectFeature::Multilingual])
                ->build()
        );

        // BASE: 90000, 130000
        // ContactForm (Light): +10000, +35000
        // Payment (Medium): +35000, +80000
        // Multilingual (Light): +10000, +35000
        // Total: min=145000, max=280000 → rounded: 145000, 280000
        self::assertSame(145_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(280_000, $result->estimateRange->maximum->amountMinor);
        self::assertCount(4, $result->oneOffItems); // BASE + 3 features
    }

    public function testOneAdvancedFeatureNoAssumption(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withFeatures([ProjectFeature::Authentication])
                ->build()
        );

        $codes = array_map(static fn($a) => $a->code(), $result->assumptions);
        self::assertNotContains('advanced_scope_requires_confirmation', $codes);
    }

    public function testTwoAdvancedFeaturesAddAssumption(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::BusinessApp)
                ->withFeatures([ProjectFeature::Authentication, ProjectFeature::RolesAndPermissions])
                ->build()
        );

        $codes = array_map(static fn($a) => $a->code(), $result->assumptions);
        self::assertContains('advanced_scope_requires_confirmation', $codes);
    }

    public function testApiIntegrationAddsExternalIntegrationAssumption(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::BusinessApp)
                ->withFeatures([ProjectFeature::ApiIntegration])
                ->build()
        );

        $codes = array_map(static fn($a) => $a->code(), $result->assumptions);
        self::assertContains('external_integration_to_confirm', $codes);
    }

    public function testAssumptionsAreDeduplicated(): void
    {
        // ApiIntegration alone: external_integration_to_confirm
        // Adding a second advanced: advanced_scope_requires_confirmation
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::BusinessApp)
                ->withFeatures([ProjectFeature::ApiIntegration, ProjectFeature::Authentication])
                ->build()
        );

        $codes = array_map(static fn($a) => $a->code(), $result->assumptions);
        self::assertContains('external_integration_to_confirm', $codes);
        self::assertContains('advanced_scope_requires_confirmation', $codes);
        self::assertSame(count($codes), count(array_unique($codes)));
    }

    // ====================================================================
    // CONTENUS
    // ====================================================================

    public function testVisualIdentityNeededAdded(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->withContentNeeds([ContentNeed::VisualIdentityNeeded])
                ->build()
        );

        // BASE 90000+50000=140000 min, 130000+100000=230000 max
        self::assertSame(140_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(230_000, $result->estimateRange->maximum->amountMinor);
        self::assertSame('CONTENT_VISUAL_IDENTITY_NEEDED', $result->oneOffItems[1]->code);
    }

    public function testVisualIdentityPartialCheaperThanFull(): void
    {
        $full = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withContentNeeds([ContentNeed::VisualIdentityNeeded])
                ->build()
        );

        $partial = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withContentNeeds([ContentNeed::VisualIdentityPartial])
                ->build()
        );

        self::assertGreaterThan($partial->estimateRange->minimum->amountMinor, $full->estimateRange->minimum->amountMinor);
        self::assertGreaterThan($partial->estimateRange->maximum->amountMinor, $full->estimateRange->maximum->amountMinor);
    }

    public function testContentToWriteAdded(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->withContentNeeds([ContentNeed::ContentToWrite])
                ->build()
        );

        // 90000+40000=130000 min, 130000+90000=220000 max → rounded: 130000, 220000
        self::assertSame(130_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(220_000, $result->estimateRange->maximum->amountMinor);
    }

    public function testPhotographyNeededAdded(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->withContentNeeds([ContentNeed::PhotographyNeeded])
                ->build()
        );

        // 90000+25000=115000 min, 130000+60000=190000 max
        self::assertSame(115_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(190_000, $result->estimateRange->maximum->amountMinor);
    }

    // ====================================================================
    // VISIBILITÉ
    // ====================================================================

    public function testSeoBasicProducesNoLineItem(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->withVisibilityNeeds([VisibilityNeed::SeoBasic])
                ->build()
        );

        // SeoBasic inclus dans socle → aucune ligne ajoutée, total inchangé
        self::assertSame(90_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(130_000, $result->estimateRange->maximum->amountMinor);
        self::assertCount(1, $result->oneOffItems);
    }

    public function testSeoAdvancedAddsLineItem(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->withVisibilityNeeds([VisibilityNeed::SeoAdvanced])
                ->build()
        );

        // 90000+30000=120000 min, 130000+80000=210000 max
        self::assertSame(120_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(210_000, $result->estimateRange->maximum->amountMinor);
        self::assertSame('VISIBILITY_SEO_ADVANCED', $result->oneOffItems[1]->code);
    }

    public function testLocalVisibilityAddsLineItem(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->withVisibilityNeeds([VisibilityNeed::LocalVisibility])
                ->build()
        );

        // 90000+20000=110000 min, 130000+50000=180000 max
        self::assertSame(110_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(180_000, $result->estimateRange->maximum->amountMinor);
    }

    public function testEditorialStrategyAddsLineItem(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->withVisibilityNeeds([VisibilityNeed::EditorialStrategy])
                ->build()
        );

        // 90000+30000=120000 min, 130000+70000=200000 max
        self::assertSame(120_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(200_000, $result->estimateRange->maximum->amountMinor);
    }

    // ====================================================================
    // RÉCURRENT — anti-doublon
    // ====================================================================

    public function testMaintenanceOnlyProducesEssentialMaintenance(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withCareNeeds([CareNeed::Maintenance])
                ->build()
        );

        self::assertCount(1, $result->recurringItems);
        self::assertSame('RECURRING_ESSENTIAL_MAINTENANCE', $result->recurringItems[0]->code);
        self::assertSame(4_900, $result->recurringItems[0]->range->minimum->amountMinor);
        self::assertSame(6_900, $result->recurringItems[0]->range->maximum->amountMinor);
    }

    public function testSupportOnlyProducesMaintenanceFollowup(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withCareNeeds([CareNeed::Support])
                ->build()
        );

        self::assertCount(1, $result->recurringItems);
        self::assertSame('RECURRING_MAINTENANCE_FOLLOWUP', $result->recurringItems[0]->code);
    }

    public function testMaintenanceAndSupportProduceSingleFollowupNoDuplication(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withCareNeeds([CareNeed::Maintenance, CareNeed::Support])
                ->build()
        );

        // Anti-doublon : Support > Maintenance → MAINTENANCE_FOLLOWUP uniquement
        self::assertCount(1, $result->recurringItems);
        self::assertSame('RECURRING_MAINTENANCE_FOLLOWUP', $result->recurringItems[0]->code);
    }

    public function testContentUpdatesProducesAccompaniment(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withCareNeeds([CareNeed::ContentUpdates])
                ->build()
        );

        self::assertCount(1, $result->recurringItems);
        self::assertSame('RECURRING_ACCOMPANIMENT', $result->recurringItems[0]->code);
    }

    public function testFunctionalEvolutionProducesRegularEvolution(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::BusinessApp)
                ->withCareNeeds([CareNeed::FunctionalEvolution])
                ->build()
        );

        self::assertCount(1, $result->recurringItems);
        self::assertSame('RECURRING_REGULAR_EVOLUTION', $result->recurringItems[0]->code);
    }

    public function testFunctionalEvolutionTakesPrecedenceOverAllOtherTiers(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::BusinessApp)
                ->withCareNeeds([
                    CareNeed::Maintenance,
                    CareNeed::Support,
                    CareNeed::ContentUpdates,
                    CareNeed::FunctionalEvolution,
                ])
                ->build()
        );

        // FunctionalEvolution takes highest tier, no duplication
        $recurringCodes = array_map(static fn($i) => $i->code, $result->recurringItems);
        self::assertNotContains('RECURRING_ESSENTIAL_MAINTENANCE', $recurringCodes);
        self::assertNotContains('RECURRING_MAINTENANCE_FOLLOWUP', $recurringCodes);
        self::assertNotContains('RECURRING_ACCOMPANIMENT', $recurringCodes);
        self::assertContains('RECURRING_REGULAR_EVOLUTION', $recurringCodes);
    }

    public function testContinuousSeoAddedSeparately(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withCareNeeds([CareNeed::ContinuousSeo])
                ->build()
        );

        self::assertCount(1, $result->recurringItems);
        self::assertSame('RECURRING_CONTINUOUS_SEO', $result->recurringItems[0]->code);
        self::assertSame(30_000, $result->recurringItems[0]->range->minimum->amountMinor);
        self::assertSame(60_000, $result->recurringItems[0]->range->maximum->amountMinor);
    }

    public function testMaintenanceAndContinuousSeoProduceTwoItems(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withCareNeeds([CareNeed::Maintenance, CareNeed::ContinuousSeo])
                ->build()
        );

        self::assertCount(2, $result->recurringItems);
        $codes = array_map(static fn($i) => $i->code, $result->recurringItems);
        self::assertContains('RECURRING_ESSENTIAL_MAINTENANCE', $codes);
        self::assertContains('RECURRING_CONTINUOUS_SEO', $codes);
    }

    public function testNoCareNeedsProducesNoRecurring(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->build()
        );

        self::assertSame([], $result->recurringItems);
        self::assertFalse($result->hasRecurringServices());
    }

    // ====================================================================
    // estimateRange = ONE-OFF UNIQUEMENT
    // ====================================================================

    public function testEstimateRangeEqualsOneOffSumRounded(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->withFeatures([ProjectFeature::ContactForm])
                ->build()
        );

        $exactMin = array_reduce(
            $result->oneOffItems,
            static fn(int $carry, $item) => $carry + $item->range->minimum->amountMinor,
            0
        );
        $exactMax = array_reduce(
            $result->oneOffItems,
            static fn(int $carry, $item) => $carry + $item->range->maximum->amountMinor,
            0
        );

        // estimateRange = rounded sum of one-off items
        self::assertLessThanOrEqual($exactMin, $result->estimateRange->minimum->amountMinor);
        self::assertGreaterThanOrEqual($exactMax, $result->estimateRange->maximum->amountMinor);
    }

    public function testRecurringItemsNotIncludedInEstimateRange(): void
    {
        $withRecurring = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->withCareNeeds([CareNeed::Maintenance])
                ->build()
        );

        $withoutRecurring = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Small)
                ->build()
        );

        // estimateRange must be identical whether or not recurring items exist
        self::assertSame(
            $withoutRecurring->estimateRange->minimum->amountMinor,
            $withRecurring->estimateRange->minimum->amountMinor,
        );
        self::assertSame(
            $withoutRecurring->estimateRange->maximum->amountMinor,
            $withRecurring->estimateRange->maximum->amountMinor,
        );
    }

    // ====================================================================
    // CURRENCY — EUR partout
    // ====================================================================

    public function testAllLineItemsAreEUR(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Ecommerce)
                ->withScale(ProjectScale::Medium)
                ->withFeatures([ProjectFeature::Payment, ProjectFeature::Shipping])
                ->withContentNeeds([ContentNeed::VisualIdentityNeeded])
                ->withVisibilityNeeds([VisibilityNeed::SeoAdvanced])
                ->withCareNeeds([CareNeed::Maintenance, CareNeed::ContinuousSeo])
                ->build()
        );

        foreach ($result->oneOffItems as $item) {
            self::assertSame('EUR', $item->range->currency());
        }
        foreach ($result->recurringItems as $item) {
            self::assertSame('EUR', $item->range->currency());
        }
        self::assertSame('EUR', $result->estimateRange->currency());
    }

    // ====================================================================
    // VERSION TARIFAIRE
    // ====================================================================

    public function testPricingVersionIs2026V1(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())->build()
        );

        self::assertSame('2026-v1', $result->pricingVersion->value());
    }

    // ====================================================================
    // UNKNOWN / OTHER
    // ====================================================================

    public function testUnknownWithSellOnlineObjectiveInfersEcommerce(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Unknown)
                ->withObjectives([ProjectObjective::SellOnline])
                ->withScale(ProjectScale::Small)
                ->build()
        );

        self::assertSame(ProjectType::Ecommerce, $result->recommendedProjectType);
        self::assertSame(170_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(250_000, $result->estimateRange->maximum->amountMinor);
    }

    public function testUnknownWithDigitalizeObjectiveInfersBusinessApp(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Unknown)
                ->withObjectives([ProjectObjective::DigitalizeProcess])
                ->withScale(ProjectScale::Small)
                ->build()
        );

        self::assertSame(ProjectType::BusinessApp, $result->recommendedProjectType);
    }

    public function testUnknownWithPresentBusinessInfersVitrineType(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Unknown)
                ->withObjectives([ProjectObjective::PresentBusiness])
                ->build()
        );

        self::assertSame(ProjectType::VitrineSite, $result->recommendedProjectType);
    }

    public function testUnknownWithGenerateLeadsInfersVitrineType(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Unknown)
                ->withObjectives([ProjectObjective::GenerateLeads])
                ->build()
        );

        self::assertSame(ProjectType::VitrineSite, $result->recommendedProjectType);
    }

    public function testUnknownWithMultipleVitrineObjectivesStillInfersVitrineType(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Unknown)
                ->withObjectives([ProjectObjective::PresentBusiness, ProjectObjective::GenerateLeads])
                ->build()
        );

        self::assertSame(ProjectType::VitrineSite, $result->recommendedProjectType);
        $codes = array_map(static fn($a) => $a->code(), $result->assumptions);
        self::assertNotContains('unknown_project_requires_human_scoping', $codes);
    }

    public function testUnknownWithSellOnlineAndImproveVisibilityInfersEcommerce(): void
    {
        // ImproveVisibility est un signal faible : il cède devant SellOnline (fort)
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Unknown)
                ->withObjectives([ProjectObjective::SellOnline, ProjectObjective::ImproveVisibility])
                ->withScale(ProjectScale::Small)
                ->build()
        );

        self::assertSame(ProjectType::Ecommerce, $result->recommendedProjectType);
        self::assertSame(170_000, $result->estimateRange->minimum->amountMinor);
        $codes = array_map(static fn($a) => $a->code(), $result->assumptions);
        self::assertNotContains('unknown_project_requires_human_scoping', $codes);
    }

    public function testUnknownWithConflictingStrongObjectivesRequiresHumanScoping(): void
    {
        // SellOnline (Ecommerce) + DigitalizeProcess (BusinessApp) → conflit → human scoping
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Unknown)
                ->withObjectives([ProjectObjective::SellOnline, ProjectObjective::DigitalizeProcess])
                ->build()
        );

        self::assertSame(ProjectType::Unknown, $result->recommendedProjectType);
        $codes = array_map(static fn($a) => $a->code(), $result->assumptions);
        self::assertContains('unknown_project_requires_human_scoping', $codes);
        self::assertGreaterThan(0, $result->estimateRange->minimum->amountMinor);
    }

    public function testUnknownWithNoInferenceAddsHumanScopingAssumption(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Unknown)
                ->withObjectives([ProjectObjective::ImproveExisting])
                ->withScale(ProjectScale::Small)
                ->build()
        );

        $codes = array_map(static fn($a) => $a->code(), $result->assumptions);
        self::assertContains('unknown_project_requires_human_scoping', $codes);
    }

    public function testUnknownFallbackRangeIsNotZero(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Unknown)
                ->withObjectives([ProjectObjective::ImproveExisting])
                ->build()
        );

        self::assertGreaterThan(0, $result->estimateRange->minimum->amountMinor);
        self::assertGreaterThan(0, $result->estimateRange->maximum->amountMinor);
    }

    public function testOtherProjectTypeAddsHumanScopingAssumption(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Other)
                ->withObjectives([ProjectObjective::ImproveExisting])
                ->build()
        );

        $codes = array_map(static fn($a) => $a->code(), $result->assumptions);
        self::assertContains('unknown_project_requires_human_scoping', $codes);
    }

    // ====================================================================
    // REFONTE
    // ====================================================================

    public function testRefonteAddsAuditAssumption(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Refonte)
                ->withScale(ProjectScale::Small)
                ->build()
        );

        $codes = array_map(static fn($a) => $a->code(), $result->assumptions);
        self::assertContains('redesign_existing_system_to_audit', $codes);
    }

    public function testRefonteStillProcessesFeatures(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::Refonte)
                ->withScale(ProjectScale::Small)
                ->withFeatures([ProjectFeature::ContentManagement])
                ->build()
        );

        // BASE 70000+10000=80000 min, 130000+35000=165000 max → rounded: 80000, 165000
        self::assertSame(80_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(165_000, $result->estimateRange->maximum->amountMinor);
        self::assertCount(2, $result->oneOffItems);
    }

    // ====================================================================
    // ARRONDI CONSERVATEUR
    // ====================================================================

    public function testRoundingNeverNarrowsRange(): void
    {
        $result = $this->engine->estimate(
            (new EstimateInputBuilder())
                ->withProjectType(ProjectType::VitrineSite)
                ->withScale(ProjectScale::Medium)
                ->withFeatures([ProjectFeature::Multilingual])
                ->build()
        );

        // BASE scaled min: 90000*115/100=103500, max: 130000*120/100=156000
        // +Multilingual (Light): +10000, +35000
        // Exact: min=113500, max=191000
        // roundMin(113500)=floor(113500/5000)*5000=22*5000=110000
        // roundMax(191000)=ceil(191000/5000)*5000=39*5000=195000
        self::assertSame(110_000, $result->estimateRange->minimum->amountMinor);
        self::assertSame(195_000, $result->estimateRange->maximum->amountMinor);

        // Min rounded <= exact min, Max rounded >= exact max
        $exactMin = 113_500;
        $exactMax = 191_000;
        self::assertLessThanOrEqual($exactMin, $result->estimateRange->minimum->amountMinor);
        self::assertGreaterThanOrEqual($exactMax, $result->estimateRange->maximum->amountMinor);
    }

    // ====================================================================
    // DÉTERMINISME
    // ====================================================================

    public function testSameInputProducesSameResult(): void
    {
        $input = (new EstimateInputBuilder())
            ->withProjectType(ProjectType::Ecommerce)
            ->withScale(ProjectScale::Medium)
            ->withFeatures([ProjectFeature::Payment, ProjectFeature::Shipping])
            ->withContentNeeds([ContentNeed::ContentToWrite])
            ->withVisibilityNeeds([VisibilityNeed::SeoAdvanced])
            ->withCareNeeds([CareNeed::Maintenance])
            ->build();

        $r1 = $this->engine->estimate($input);
        $r2 = $this->engine->estimate($input);

        self::assertSame($r1->estimateRange->minimum->amountMinor, $r2->estimateRange->minimum->amountMinor);
        self::assertSame($r1->estimateRange->maximum->amountMinor, $r2->estimateRange->maximum->amountMinor);
        self::assertSame($r1->pricingVersion->value(), $r2->pricingVersion->value());
        self::assertCount(count($r1->oneOffItems), $r2->oneOffItems);
    }
}
