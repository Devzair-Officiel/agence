<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain\Lead;

use App\Estimator\Domain\Lead\EstimatorLead;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(EstimatorLead::class)]
final class EstimatorLeadTest extends TestCase
{
    private function makeId(): Uuid
    {
        return Uuid::v7();
    }

    private function makeNow(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('2026-08-30T12:00:00+00:00');
    }

    /** @return array<string, mixed> */
    private function questionnaireSnapshot(): array
    {
        return [
            'project_type'      => 'vitrinesite',
            'objectives'        => [],
            'current_situation' => 'none',
            'scale'             => 'small',
            'features'          => [],
            'content_needs'     => [],
            'visibility_needs'  => [],
            'care_needs'        => [],
        ];
    }

    /** @return array<string, mixed> */
    private function estimateSnapshot(): array
    {
        return [
            'outcome'                  => 'estimated',
            'recommended_project_type' => 'vitrinesite',
            'estimate'                 => ['minimum' => 90_000, 'maximum' => 130_000, 'currency' => 'EUR'],
            'pricing_version'          => '2026-v1',
        ];
    }

    public function testCreateEstimatedLeadStoresAllFields(): void
    {
        $id  = $this->makeId();
        $now = $this->makeNow();

        $lead = EstimatorLead::create(
            id:                    $id,
            name:                  'Jean Dupont',
            email:                 'jean@example.com',
            phone:                 '+33 6 12 34 56 78',
            company:               'ACME',
            additionalFeatureNote: 'Besoin ERP',
            projectType:           'vitrinesite',
            outcome:               'estimated',
            estimateMinimumMinor:  90_000,
            estimateMaximumMinor:  130_000,
            pricingVersion:        '2026-v1',
            questionnaireSnapshot: $this->questionnaireSnapshot(),
            estimateSnapshot:      $this->estimateSnapshot(),
            requestId:             'req-abc-123',
            createdAt:             $now,
        );

        self::assertSame('Jean Dupont', $lead->name());
        self::assertSame('jean@example.com', $lead->email());
        self::assertSame('+33 6 12 34 56 78', $lead->phone());
        self::assertSame('ACME', $lead->company());
        self::assertSame('Besoin ERP', $lead->additionalFeatureNote());
        self::assertSame('vitrinesite', $lead->projectType());
        self::assertSame('estimated', $lead->outcome());
        self::assertSame(90_000, $lead->estimateMinimumMinor());
        self::assertSame(130_000, $lead->estimateMaximumMinor());
        self::assertSame('2026-v1', $lead->pricingVersion());
        self::assertSame('req-abc-123', $lead->requestId());
        self::assertSame($now, $lead->createdAt());
        self::assertTrue($id->equals($lead->id()));
    }

    public function testHumanScopingLeadHasNullAmounts(): void
    {
        $lead = EstimatorLead::create(
            id:                    $this->makeId(),
            name:                  'Marie Martin',
            email:                 'marie@example.com',
            phone:                 null,
            company:               null,
            additionalFeatureNote: null,
            projectType:           'unknown',
            outcome:               'human_scoping_required',
            estimateMinimumMinor:  null,
            estimateMaximumMinor:  null,
            pricingVersion:        '2026-v1',
            questionnaireSnapshot: $this->questionnaireSnapshot(),
            estimateSnapshot:      ['outcome' => 'human_scoping_required'],
            requestId:             'req-xyz-456',
            createdAt:             $this->makeNow(),
        );

        self::assertNull($lead->estimateMinimumMinor());
        self::assertNull($lead->estimateMaximumMinor());
        self::assertNull($lead->phone());
        self::assertNull($lead->company());
        self::assertNull($lead->additionalFeatureNote());
    }

    public function testEmptyStringPhoneIsNormalisedToNull(): void
    {
        $lead = EstimatorLead::create(
            id:                    $this->makeId(),
            name:                  'Test',
            email:                 'test@example.com',
            phone:                 '',
            company:               '',
            additionalFeatureNote: '',
            projectType:           'vitrinesite',
            outcome:               'estimated',
            estimateMinimumMinor:  90_000,
            estimateMaximumMinor:  130_000,
            pricingVersion:        '2026-v1',
            questionnaireSnapshot: $this->questionnaireSnapshot(),
            estimateSnapshot:      $this->estimateSnapshot(),
            requestId:             'req-empty',
            createdAt:             $this->makeNow(),
        );

        self::assertNull($lead->phone());
        self::assertNull($lead->company());
        self::assertNull($lead->additionalFeatureNote());
    }

    public function testQuestionnaireSnapshotContainsNoPrice(): void
    {
        $snapshot = $this->questionnaireSnapshot();

        self::assertArrayNotHasKey('estimate', $snapshot);
        self::assertArrayNotHasKey('name', $snapshot);
        self::assertArrayNotHasKey('email', $snapshot);
        self::assertArrayHasKey('project_type', $snapshot);
        self::assertArrayHasKey('features', $snapshot);
    }
}
