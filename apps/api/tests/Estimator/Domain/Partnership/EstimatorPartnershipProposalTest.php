<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain\Partnership;

use App\Estimator\Domain\Partnership\EstimatorPartnershipProposal;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(EstimatorPartnershipProposal::class)]
final class EstimatorPartnershipProposalTest extends TestCase
{
    private function makeProposal(array $overrides = []): EstimatorPartnershipProposal
    {
        $defaults = [
            'id'                   => Uuid::v7(),
            'leadRequestId'        => null,
            'name'                 => 'Jean Dupont',
            'email'                => 'jean@example.com',
            'phone'                => null,
            'company'              => null,
            'projectType'          => 'vitrinesite',
            'pricingVersion'       => '2026-v1',
            'estimateMinimumMinor' => 90_000,
            'estimateMaximumMinor' => 130_000,
            'currency'             => 'EUR',
            'partnershipType'      => 'revenue_share',
            'projectStage'         => 'launched',
            'generatesRevenue'     => 'yes',
            'proposalText'         => 'Je propose un partage de revenus de mon activité.',
            'relevanceText'        => null,
            'questionnaireSnapshot'=> ['project_type' => 'vitrinesite'],
            'estimateSnapshot'     => ['outcome' => 'estimated'],
            'requestId'            => 'req-test-001',
            'createdAt'            => new \DateTimeImmutable('2026-08-30T12:00:00+00:00'),
        ];

        $args = array_merge($defaults, $overrides);

        return EstimatorPartnershipProposal::create(...$args);
    }

    public function testCreateEstimatedProposalHasCorrectData(): void
    {
        $proposal = $this->makeProposal();

        self::assertSame('Jean Dupont', $proposal->name());
        self::assertSame('jean@example.com', $proposal->email());
        self::assertSame('revenue_share', $proposal->partnershipType());
        self::assertSame('launched', $proposal->projectStage());
        self::assertSame('yes', $proposal->generatesRevenue());
        self::assertSame(90_000, $proposal->estimateMinimumMinor());
        self::assertSame(130_000, $proposal->estimateMaximumMinor());
        self::assertSame('EUR', $proposal->currency());
    }

    public function testStatusIsAlwaysPendingReviewAtCreation(): void
    {
        $proposal = $this->makeProposal();

        self::assertSame(EstimatorPartnershipProposal::STATUS_PENDING_REVIEW, $proposal->status());
        self::assertSame('pending_review', $proposal->status());
    }

    public function testHumanScopingHasNullAmounts(): void
    {
        $proposal = $this->makeProposal([
            'estimateMinimumMinor' => null,
            'estimateMaximumMinor' => null,
            'currency'             => null,
        ]);

        self::assertNull($proposal->estimateMinimumMinor());
        self::assertNull($proposal->estimateMaximumMinor());
        self::assertNull($proposal->currency());
    }

    public function testEmptyStringNormalizedToNull(): void
    {
        $proposal = $this->makeProposal([
            'phone'            => '',
            'company'          => '',
            'generatesRevenue' => '',
            'relevanceText'    => '',
            'leadRequestId'    => '',
        ]);

        self::assertNull($proposal->phone());
        self::assertNull($proposal->company());
        self::assertNull($proposal->generatesRevenue());
        self::assertNull($proposal->relevanceText());
        self::assertNull($proposal->leadRequestId());
    }

    public function testQuestionnaireSnapshotContainsNoContactFields(): void
    {
        $snapshot = ['project_type' => 'ecommerce', 'scale' => 'medium'];
        $proposal = $this->makeProposal(['questionnaireSnapshot' => $snapshot]);

        $data = $proposal->questionnaireSnapshot();

        self::assertArrayHasKey('project_type', $data);
        self::assertArrayNotHasKey('name', $data);
        self::assertArrayNotHasKey('email', $data);
        self::assertArrayNotHasKey('phone', $data);
        self::assertArrayNotHasKey('company', $data);
        self::assertArrayNotHasKey('proposal_text', $data);
        self::assertArrayNotHasKey('partnership_type', $data);
    }

    public function testNoAutomaticPercentageOrEquityField(): void
    {
        $proposal = $this->makeProposal();

        // Garantie : aucun champ de calcul de parts n'existe sur l'entité.
        self::assertFalse(property_exists($proposal, 'percentage'));
        self::assertFalse(property_exists($proposal, 'equity'));
        self::assertFalse(property_exists($proposal, 'commissionRate'));
        self::assertFalse(property_exists($proposal, 'revenueShareRate'));
        self::assertFalse(property_exists($proposal, 'valuation'));
        self::assertFalse(property_exists($proposal, 'durationMonths'));
    }

    public function testLeadRequestIdStoredCorrectly(): void
    {
        $proposal = $this->makeProposal(['leadRequestId' => 'req-lead-abc123']);

        self::assertSame('req-lead-abc123', $proposal->leadRequestId());
    }

    public function testAllPartnershipTypesAreValid(): void
    {
        $validTypes = ['revenue_share', 'equity_stake', 'commercial_collaboration', 'service_exchange', 'other'];

        foreach ($validTypes as $type) {
            $proposal = $this->makeProposal(['partnershipType' => $type]);
            self::assertSame($type, $proposal->partnershipType());
        }
    }

    public function testAllProjectStagesAreValid(): void
    {
        $validStages = ['idea', 'in_creation', 'launched', 'with_clients', 'established'];

        foreach ($validStages as $stage) {
            $proposal = $this->makeProposal(['projectStage' => $stage]);
            self::assertSame($stage, $proposal->projectStage());
        }
    }
}
