<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Support;

use App\Estimator\Domain\Pricing\PricingConfiguration;
use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Insère une configuration tarifaire publiée dans la base de test.
 * Chaque test qui en a besoin appelle seed() pour être auto-suffisant,
 * indépendamment de l'ordre d'exécution global.
 */
final class EstimatorPricingSeeder
{
    public static function seed(ContainerInterface $container): void
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $em->getConnection()->executeStatement('TRUNCATE TABLE estimator_pricing_configuration');
        $em->clear();

        $config = PricingConfiguration::create(
            id:            Uuid::v7(),
            version:       '2026-v1',
            currency:      'EUR',
            configuration: self::v1Config(),
        );
        $config->publish();

        /** @var PricingConfigurationRepositoryInterface $repo */
        $repo = $container->get(PricingConfigurationRepositoryInterface::class);
        $repo->save($config);
    }

    /** @return array<string, mixed> */
    private static function v1Config(): array
    {
        return [
            'base_amounts'       => [
                'vitrinesite' => ['min' => 90_000, 'max' => 130_000],
                'ecommerce'   => ['min' => 170_000, 'max' => 250_000],
                'businessapp' => ['min' => 250_000, 'max' => 400_000],
                'refonte'     => ['min' => 70_000, 'max' => 130_000],
            ],
            'scale_percents'     => [
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
                'light'    => ['min' => 10_000, 'max' => 35_000],
                'medium'   => ['min' => 35_000, 'max' => 80_000],
                'advanced' => ['min' => 70_000, 'max' => 180_000],
            ],
            'content_amounts'    => [
                'visual_identity_needed'  => ['min' => 50_000, 'max' => 100_000],
                'visual_identity_partial' => ['min' => 25_000, 'max' => 50_000],
                'content_to_write'        => ['min' => 40_000, 'max' => 90_000],
                'content_with_help'       => ['min' => 15_000, 'max' => 40_000],
                'photography_needed'      => ['min' => 25_000, 'max' => 60_000],
            ],
            'visibility_amounts' => [
                'seo_basic'          => null,
                'seo_advanced'       => ['min' => 30_000, 'max' => 80_000],
                'local_visibility'   => ['min' => 20_000, 'max' => 50_000],
                'editorial_strategy' => ['min' => 30_000, 'max' => 70_000],
            ],
            'recurring_amounts'  => [
                'ESSENTIAL_MAINTENANCE' => ['min' => 4_900, 'max' => 6_900],
                'MAINTENANCE_FOLLOWUP'  => ['min' => 8_900, 'max' => 12_900],
                'ACCOMPANIMENT'         => ['min' => 14_900, 'max' => 21_900],
                'REGULAR_EVOLUTION'     => ['min' => 24_900, 'max' => 39_900],
                'CONTINUOUS_SEO'        => ['min' => 30_000, 'max' => 60_000],
            ],
        ];
    }
}
