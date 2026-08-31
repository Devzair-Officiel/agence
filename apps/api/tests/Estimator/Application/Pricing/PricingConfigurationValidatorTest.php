<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Application\Pricing;

use App\Estimator\Application\Pricing\PricingCatalogDefinition;
use App\Estimator\Application\Pricing\PricingConfigurationValidator;
use PHPUnit\Framework\TestCase;

final class PricingConfigurationValidatorTest extends TestCase
{
    private PricingConfigurationValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new PricingConfigurationValidator();
    }

    private function validDefinition(): PricingCatalogDefinition
    {
        return PricingCatalogDefinition::fromArray('test', 'EUR', [
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
    }

    public function testValidDefinitionProducesNoErrors(): void
    {
        $errors = $this->validator->validate($this->validDefinition());
        self::assertSame([], $errors);
    }

    public function testRejectsCurrencyOtherThanEur(): void
    {
        $def    = PricingCatalogDefinition::fromArray('test', 'USD', [
            'base_amounts'       => [],
            'scale_percents'     => [],
            'feature_complexity' => [],
            'complexity_amounts' => [],
            'content_amounts'    => [],
            'visibility_amounts' => [],
            'recurring_amounts'  => [],
        ]);
        $errors = $this->validator->validate($def);
        self::assertNotEmpty($errors);
        self::assertStringContainsString('EUR', $errors[0]);
    }

    public function testDetectsMissingBaseAmount(): void
    {
        $raw = [
            'base_amounts' => [
                // 'vitrinesite' manquant
                'ecommerce'   => ['min' => 170_000, 'max' => 250_000],
                'businessapp' => ['min' => 250_000, 'max' => 400_000],
                'refonte'     => ['min' => 70_000,  'max' => 130_000],
            ],
            'scale_percents'     => ['small' => ['min' => 100, 'max' => 100], 'medium' => ['min' => 115, 'max' => 120], 'large' => ['min' => 130, 'max' => 145], 'unknown' => ['min' => 100, 'max' => 100]],
            'feature_complexity' => ['contact_form' => 'light', 'multilingual' => 'light', 'content_management' => 'light', 'booking_form' => 'medium', 'payment' => 'medium', 'shipping' => 'medium', 'customer_accounts' => 'medium', 'notifications' => 'medium', 'import_export' => 'medium', 'stock_management' => 'medium', 'promotions' => 'medium', 'authentication' => 'advanced', 'roles_and_permissions' => 'advanced', 'document_generation' => 'advanced', 'api_integration' => 'advanced'],
            'complexity_amounts' => ['light' => ['min' => 10_000, 'max' => 35_000], 'medium' => ['min' => 35_000, 'max' => 80_000], 'advanced' => ['min' => 70_000, 'max' => 180_000]],
            'content_amounts'    => ['visual_identity_needed' => ['min' => 50_000, 'max' => 100_000], 'visual_identity_partial' => ['min' => 25_000, 'max' => 50_000], 'content_to_write' => ['min' => 40_000, 'max' => 90_000], 'content_with_help' => ['min' => 15_000, 'max' => 40_000], 'photography_needed' => ['min' => 25_000, 'max' => 60_000]],
            'visibility_amounts' => ['seo_basic' => null, 'seo_advanced' => ['min' => 30_000, 'max' => 80_000], 'local_visibility' => ['min' => 20_000, 'max' => 50_000], 'editorial_strategy' => ['min' => 30_000, 'max' => 70_000]],
            'recurring_amounts'  => ['ESSENTIAL_MAINTENANCE' => ['min' => 4_900, 'max' => 6_900], 'MAINTENANCE_FOLLOWUP' => ['min' => 8_900, 'max' => 12_900], 'ACCOMPANIMENT' => ['min' => 14_900, 'max' => 21_900], 'REGULAR_EVOLUTION' => ['min' => 24_900, 'max' => 39_900], 'CONTINUOUS_SEO' => ['min' => 30_000, 'max' => 60_000]],
        ];

        $def    = PricingCatalogDefinition::fromArray('test', 'EUR', $raw);
        $errors = $this->validator->validate($def);
        self::assertTrue(
            count(array_filter($errors, fn($e) => str_contains($e, 'vitrinesite'))) > 0,
            'Expected error about missing vitrinesite base_amount',
        );
    }

    public function testDetectsInvertedRange(): void
    {
        $raw = $this->rawValidData();
        $raw['base_amounts']['vitrinesite'] = ['min' => 200_000, 'max' => 100_000];

        $def    = PricingCatalogDefinition::fromArray('test', 'EUR', $raw);
        $errors = $this->validator->validate($def);

        self::assertTrue(
            count(array_filter($errors, fn($e) => str_contains($e, 'min') && str_contains($e, 'max'))) > 0,
            'Expected error about inverted range',
        );
    }

    public function testSeoBasicMustBeNull(): void
    {
        $raw = $this->rawValidData();
        $raw['visibility_amounts']['seo_basic'] = ['min' => 10_000, 'max' => 20_000];

        $def    = PricingCatalogDefinition::fromArray('test', 'EUR', $raw);
        $errors = $this->validator->validate($def);

        self::assertTrue(
            count(array_filter($errors, fn($e) => str_contains($e, 'seo_basic'))) > 0,
            'Expected error about seo_basic not being null',
        );
    }

    public function testDetectsUnknownFeatureComplexity(): void
    {
        $raw = $this->rawValidData();
        $raw['feature_complexity']['contact_form'] = 'unknown_complexity';

        $def    = PricingCatalogDefinition::fromArray('test', 'EUR', $raw);
        $errors = $this->validator->validate($def);

        self::assertNotEmpty($errors);
    }

    /**
     * @return array<string, mixed>
     */
    private function rawValidData(): array
    {
        return [
            'base_amounts' => [
                'vitrinesite' => ['min' => 90_000, 'max' => 130_000],
                'ecommerce'   => ['min' => 170_000, 'max' => 250_000],
                'businessapp' => ['min' => 250_000, 'max' => 400_000],
                'refonte'     => ['min' => 70_000, 'max' => 130_000],
            ],
            'scale_percents'     => ['small' => ['min' => 100, 'max' => 100], 'medium' => ['min' => 115, 'max' => 120], 'large' => ['min' => 130, 'max' => 145], 'unknown' => ['min' => 100, 'max' => 100]],
            'feature_complexity' => ['contact_form' => 'light', 'multilingual' => 'light', 'content_management' => 'light', 'booking_form' => 'medium', 'payment' => 'medium', 'shipping' => 'medium', 'customer_accounts' => 'medium', 'notifications' => 'medium', 'import_export' => 'medium', 'stock_management' => 'medium', 'promotions' => 'medium', 'authentication' => 'advanced', 'roles_and_permissions' => 'advanced', 'document_generation' => 'advanced', 'api_integration' => 'advanced'],
            'complexity_amounts' => ['light' => ['min' => 10_000, 'max' => 35_000], 'medium' => ['min' => 35_000, 'max' => 80_000], 'advanced' => ['min' => 70_000, 'max' => 180_000]],
            'content_amounts'    => ['visual_identity_needed' => ['min' => 50_000, 'max' => 100_000], 'visual_identity_partial' => ['min' => 25_000, 'max' => 50_000], 'content_to_write' => ['min' => 40_000, 'max' => 90_000], 'content_with_help' => ['min' => 15_000, 'max' => 40_000], 'photography_needed' => ['min' => 25_000, 'max' => 60_000]],
            'visibility_amounts' => ['seo_basic' => null, 'seo_advanced' => ['min' => 30_000, 'max' => 80_000], 'local_visibility' => ['min' => 20_000, 'max' => 50_000], 'editorial_strategy' => ['min' => 30_000, 'max' => 70_000]],
            'recurring_amounts'  => ['ESSENTIAL_MAINTENANCE' => ['min' => 4_900, 'max' => 6_900], 'MAINTENANCE_FOLLOWUP' => ['min' => 8_900, 'max' => 12_900], 'ACCOMPANIMENT' => ['min' => 14_900, 'max' => 21_900], 'REGULAR_EVOLUTION' => ['min' => 24_900, 'max' => 39_900], 'CONTINUOUS_SEO' => ['min' => 30_000, 'max' => 60_000]],
        ];
    }
}
