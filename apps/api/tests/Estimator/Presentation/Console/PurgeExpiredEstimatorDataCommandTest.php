<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Presentation\Console;

use App\Estimator\Domain\Lead\EstimatorLead;
use App\Estimator\Domain\Lead\EstimatorLeadRepositoryInterface;
use App\Estimator\Domain\Partnership\EstimatorPartnershipProposal;
use App\Estimator\Domain\Partnership\EstimatorPartnershipRepositoryInterface;
use App\Estimator\Presentation\Console\PurgeExpiredEstimatorDataCommand;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Uid\Uuid;

/**
 * Tests d'intégration pour app:estimator:purge-expired.
 *
 * Vérifie :
 * - dry-run : counts corrects, aucune suppression ;
 * - purge réelle : expirés supprimés, récents conservés ;
 * - frontière : 23 mois conservé, 25 mois supprimé ;
 * - aucun PII dans la sortie (nom, email, téléphone, société, texte).
 */
final class PurgeExpiredEstimatorDataCommandTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private EstimatorLeadRepositoryInterface $leadRepo;
    private EstimatorPartnershipRepositoryInterface $partnershipRepo;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        self::bootKernel();
        $container             = self::getContainer();
        $this->em              = $container->get(EntityManagerInterface::class);
        $this->leadRepo        = $container->get(EstimatorLeadRepositoryInterface::class);
        $this->partnershipRepo = $container->get(EstimatorPartnershipRepositoryInterface::class);

        $this->purgeEstimatorTables();

        // Construct directly to avoid Symfony's lazy-proxy wrapping of console commands
        // in the test service container.
        $command             = new PurgeExpiredEstimatorDataCommand(
            $this->leadRepo,
            $this->partnershipRepo,
            $this->em,
            new MockClock(new \DateTimeImmutable('now')),
            new NullLogger(),
        );
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        $this->purgeEstimatorTables();
        parent::tearDown();
    }

    // ── Dry-run ────────────────────────────────────────────────────────────

    public function testDryRunReportsCountsWithoutDeleting(): void
    {
        $this->seedLead('old@example.com', new \DateTimeImmutable('-25 months'));
        $this->seedLead('recent@example.com', new \DateTimeImmutable('-6 months'));
        $this->seedPartnership('old-partner@example.com', new \DateTimeImmutable('-26 months'));

        $this->commandTester->execute(['--dry-run' => true]);

        $display = $this->commandTester->getDisplay();
        self::assertSame(0, $this->commandTester->getStatusCode());
        self::assertStringContainsString('DRY-RUN', $display);
        self::assertStringContainsString('1', $display); // 1 lead expiré
        self::assertStringContainsString('1', $display); // 1 partenariat expiré

        // Aucune donnée supprimée
        $this->em->clear();
        self::assertCount(2, $this->leadRepo->listAll());
        self::assertCount(1, $this->partnershipRepo->listAll());
    }

    public function testDryRunOutputContainsNoPii(): void
    {
        $this->seedLead('secret@example.com', new \DateTimeImmutable('-30 months'));

        $this->commandTester->execute(['--dry-run' => true]);

        $display = $this->commandTester->getDisplay();
        self::assertStringNotContainsString('secret@example.com', $display);
        self::assertStringNotContainsString('Jean Dupont', $display);
    }

    // ── Purge réelle ───────────────────────────────────────────────────────

    public function testExpiredLeadIsDeletedAndRecentIsKept(): void
    {
        $recentId = Uuid::v7();
        $this->seedLead('recent@example.com', new \DateTimeImmutable('-6 months'), $recentId);
        $this->seedLead('old@example.com', new \DateTimeImmutable('-25 months'));

        $this->commandTester->execute([]);

        self::assertSame(0, $this->commandTester->getStatusCode());

        $this->em->clear();
        self::assertNotNull($this->leadRepo->findById($recentId));
        self::assertCount(1, $this->leadRepo->listAll());
    }

    public function testExpiredPartnershipIsDeletedAndRecentIsKept(): void
    {
        $recentId = Uuid::v7();
        $this->seedPartnership('recent@example.com', new \DateTimeImmutable('-3 months'), $recentId);
        $this->seedPartnership('old@example.com', new \DateTimeImmutable('-25 months'));

        $this->commandTester->execute([]);

        self::assertSame(0, $this->commandTester->getStatusCode());

        $this->em->clear();
        self::assertNotNull($this->partnershipRepo->findById($recentId));
        self::assertCount(1, $this->partnershipRepo->listAll());
    }

    public function testPurgeOutputContainsNoPii(): void
    {
        $this->seedLead('private@example.com', new \DateTimeImmutable('-30 months'));

        $this->commandTester->execute([]);

        $display = $this->commandTester->getDisplay();
        self::assertStringNotContainsString('private@example.com', $display);
        self::assertStringNotContainsString('Jean Dupont', $display);
    }

    // ── Frontière exacte (23 vs 25 mois) ──────────────────────────────────

    public function testLeadAt23MonthsIsConserved(): void
    {
        $id = Uuid::v7();
        $this->seedLead('border@example.com', new \DateTimeImmutable('-23 months'), $id);

        $this->commandTester->execute([]);

        $this->em->clear();
        self::assertNotNull($this->leadRepo->findById($id), 'Lead à 23 mois doit être conservé.');
    }

    public function testLeadAt25MonthsIsDeleted(): void
    {
        $id = Uuid::v7();
        $this->seedLead('expired@example.com', new \DateTimeImmutable('-25 months'), $id);

        $this->commandTester->execute([]);

        $this->em->clear();
        self::assertNull($this->leadRepo->findById($id), 'Lead à 25 mois doit être supprimé.');
    }

    public function testPartnershipAt23MonthsIsConserved(): void
    {
        $id = Uuid::v7();
        $this->seedPartnership('border@example.com', new \DateTimeImmutable('-23 months'), $id);

        $this->commandTester->execute([]);

        $this->em->clear();
        self::assertNotNull($this->partnershipRepo->findById($id), 'Proposal à 23 mois doit être conservé.');
    }

    public function testPartnershipAt25MonthsIsDeleted(): void
    {
        $id = Uuid::v7();
        $this->seedPartnership('expired@example.com', new \DateTimeImmutable('-25 months'), $id);

        $this->commandTester->execute([]);

        $this->em->clear();
        self::assertNull($this->partnershipRepo->findById($id), 'Proposal à 25 mois doit être supprimé.');
    }

    // ── Relation lead ↔ partnership : pas de FK cassée ────────────────────

    public function testNonExpiredPartnershipKeepsIntegrityWhenLeadExpires(): void
    {
        // Lead expiré (25 mois)
        $oldLeadRequestId = 'req-old-lead-001';
        $this->seedLeadWithRequestId($oldLeadRequestId, new \DateTimeImmutable('-25 months'));

        // Partnership récent (6 mois) qui référence le lead via lead_request_id
        $partnershipId = Uuid::v7();
        $this->seedPartnershipWithLeadRef($oldLeadRequestId, new \DateTimeImmutable('-6 months'), $partnershipId);

        // La purge supprime le lead mais pas la partnership
        $this->commandTester->execute([]);
        self::assertSame(0, $this->commandTester->getStatusCode());

        $this->em->clear();
        // Le lead est supprimé
        self::assertCount(0, $this->leadRepo->listAll());
        // La partnership est conservée (pas de FK → pas d'erreur)
        self::assertNotNull($this->partnershipRepo->findById($partnershipId));
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function purgeEstimatorTables(): void
    {
        /** @var Connection $conn */
        $conn = $this->em->getConnection();
        $conn->executeStatement('TRUNCATE TABLE estimator_lead');
        $conn->executeStatement('TRUNCATE TABLE estimator_partnership_proposal');
        $this->em->clear();
    }

    private function seedLead(
        string $email,
        \DateTimeImmutable $createdAt,
        ?Uuid $id = null,
    ): EstimatorLead {
        return $this->seedLeadWithRequestId(Uuid::v7()->toRfc4122(), $createdAt, $id ?? Uuid::v7(), $email);
    }

    private function seedLeadWithRequestId(
        string $requestId,
        \DateTimeImmutable $createdAt,
        ?Uuid $id = null,
        string $email = 'test@example.com',
    ): EstimatorLead {
        $lead = EstimatorLead::create(
            id:                    $id ?? Uuid::v7(),
            name:                  'Jean Dupont',
            email:                 $email,
            phone:                 null,
            company:               null,
            additionalFeatureNote: null,
            projectType:           'vitrinesite',
            outcome:               'estimated',
            estimateMinimumMinor:  90_000,
            estimateMaximumMinor:  130_000,
            pricingVersion:        '2026-v1',
            questionnaireSnapshot: ['type' => 'vitrinesite'],
            estimateSnapshot:      ['min' => 90_000, 'max' => 130_000],
            requestId:             $requestId,
            createdAt:             $createdAt,
        );
        $this->leadRepo->save($lead);
        $this->em->clear();

        return $lead;
    }

    private function seedPartnership(
        string $email,
        \DateTimeImmutable $createdAt,
        ?Uuid $id = null,
    ): EstimatorPartnershipProposal {
        return $this->seedPartnershipWithLeadRef(null, $createdAt, $id ?? Uuid::v7(), $email);
    }

    private function seedPartnershipWithLeadRef(
        ?string $leadRequestId,
        \DateTimeImmutable $createdAt,
        ?Uuid $id = null,
        string $email = 'partner@example.com',
    ): EstimatorPartnershipProposal {
        $proposal = EstimatorPartnershipProposal::create(
            id:                    $id ?? Uuid::v7(),
            leadRequestId:         $leadRequestId,
            name:                  'Alice Partenaire',
            email:                 $email,
            phone:                 null,
            company:               'Test SAS',
            projectType:           'vitrinesite',
            pricingVersion:        '2026-v1',
            estimateMinimumMinor:  90_000,
            estimateMaximumMinor:  130_000,
            currency:              'EUR',
            partnershipType:       'revenue_sharing',
            projectStage:          'idea',
            generatesRevenue:      null,
            proposalText:          'Proposition test.',
            relevanceText:         null,
            questionnaireSnapshot: ['type' => 'vitrinesite'],
            estimateSnapshot:      ['min' => 90_000],
            requestId:             Uuid::v7()->toRfc4122(),
            createdAt:             $createdAt,
        );
        $this->partnershipRepo->save($proposal);
        $this->em->clear();

        return $proposal;
    }
}
