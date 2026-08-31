<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain\Pricing;

use App\Estimator\Domain\Pricing\Exception\InvalidPricingTransitionException;
use App\Estimator\Domain\Pricing\Exception\PricingConfigurationImmutableException;
use App\Estimator\Domain\Pricing\PricingConfiguration;
use App\Estimator\Domain\Pricing\PricingConfigurationStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class PricingConfigurationTest extends TestCase
{
    private function makeConfig(string $version = '2026-v1'): PricingConfiguration
    {
        return PricingConfiguration::create(
            id:            Uuid::v7(),
            version:       $version,
            currency:      'EUR',
            configuration: ['base_amounts' => ['vitrinesite' => ['min' => 90_000, 'max' => 130_000]]],
        );
    }

    // ─── Création ───────────────────────────────────────────────────────────

    public function testCreatedConfigIsDraft(): void
    {
        $config = $this->makeConfig();
        self::assertSame(PricingConfigurationStatus::Draft, $config->status());
    }

    public function testCreatedConfigHasCorrectVersion(): void
    {
        $config = $this->makeConfig('my-version');
        self::assertSame('my-version', $config->version());
    }

    public function testCreatedConfigHasNoPublishedAt(): void
    {
        $config = $this->makeConfig();
        self::assertNull($config->publishedAt());
    }

    // ─── updateConfiguration ─────────────────────────────────────────────

    public function testDraftCanBeUpdated(): void
    {
        $config    = $this->makeConfig();
        $newData   = ['base_amounts' => ['ecommerce' => ['min' => 200_000, 'max' => 300_000]]];
        $config->updateConfiguration($newData);

        self::assertSame($newData, $config->configuration());
    }

    public function testPublishedConfigThrowsOnUpdate(): void
    {
        $config = $this->makeConfig();
        $config->publish();

        $this->expectException(PricingConfigurationImmutableException::class);
        $config->updateConfiguration(['base_amounts' => []]);
    }

    public function testArchivedConfigThrowsOnUpdate(): void
    {
        $config = $this->makeConfig();
        $config->publish();
        $config->archive();

        $this->expectException(PricingConfigurationImmutableException::class);
        $config->updateConfiguration([]);
    }

    // ─── publish ─────────────────────────────────────────────────────────

    public function testPublishTransitionsDraftToPublished(): void
    {
        $config = $this->makeConfig();
        $adminId = Uuid::v7();

        $config->publish($adminId);

        self::assertSame(PricingConfigurationStatus::Published, $config->status());
        self::assertNotNull($config->publishedAt());
        self::assertSame($adminId->toRfc4122(), $config->publishedBy()?->toRfc4122());
    }

    public function testPublishThrowsIfAlreadyPublished(): void
    {
        $config = $this->makeConfig();
        $config->publish();

        $this->expectException(InvalidPricingTransitionException::class);
        $config->publish();
    }

    public function testPublishThrowsIfArchived(): void
    {
        $config = $this->makeConfig();
        $config->publish();
        $config->archive();

        $this->expectException(InvalidPricingTransitionException::class);
        $config->publish();
    }

    // ─── archive ─────────────────────────────────────────────────────────

    public function testArchiveTransitionsPublishedToArchived(): void
    {
        $config = $this->makeConfig();
        $config->publish();
        $config->archive();

        self::assertSame(PricingConfigurationStatus::Archived, $config->status());
    }

    public function testArchiveThrowsIfDraft(): void
    {
        $config = $this->makeConfig();

        $this->expectException(\DomainException::class);
        $config->archive();
    }

    public function testArchiveThrowsIfAlreadyArchived(): void
    {
        $config = $this->makeConfig();
        $config->publish();
        $config->archive();

        $this->expectException(\DomainException::class);
        $config->archive();
    }

    // ─── Status helpers ───────────────────────────────────────────────────

    public function testDraftIsEditable(): void
    {
        self::assertTrue(PricingConfigurationStatus::Draft->isEditable());
    }

    public function testPublishedIsNotEditable(): void
    {
        self::assertFalse(PricingConfigurationStatus::Published->isEditable());
    }

    public function testArchivedIsNotEditable(): void
    {
        self::assertFalse(PricingConfigurationStatus::Archived->isEditable());
    }
}
