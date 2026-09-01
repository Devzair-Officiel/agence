<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http\Estimator;

use App\Estimator\Domain\Partnership\EstimatorPartnershipProposal;
use App\Estimator\Domain\Partnership\EstimatorPartnershipRepositoryInterface;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use App\Tests\Admin\Support\AdminHttpTestHelper;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Tests d'intégration pour l'administration des propositions de partenariat (EST-9).
 *
 * Vérifie :
 * - Contrôle d'accès : non authentifié → redirect login
 * - Liste : renvoie 200 avec la proposition seeded
 * - Détail : renvoie 200 avec les données de la proposition
 * - Détail : UUID invalide → 404
 * - Détail : UUID inconnu → 404
 */
final class AdminEstimatorPartnershipControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private EstimatorPartnershipRepositoryInterface $repo;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em     = self::getContainer()->get(EntityManagerInterface::class);
        $this->repo   = self::getContainer()->get(EstimatorPartnershipRepositoryInterface::class);
        AdminDatabaseCleanup::purge($this->em);
        $this->purgePartnershipTable();
    }

    // ─── Contrôle d'accès ────────────────────────────────────────────────

    public function testListRedirectsUnauthenticatedToLogin(): void
    {
        $this->client->request('GET', '/admin/estimator/partnerships');
        self::assertResponseRedirects('/admin/login');
    }

    public function testShowRedirectsUnauthenticatedToLogin(): void
    {
        $this->client->request('GET', '/admin/estimator/partnerships/'.Uuid::v7()->toRfc4122());
        self::assertResponseRedirects('/admin/login');
    }

    // ─── Liste ───────────────────────────────────────────────────────────

    public function testListReturnsOkWhenEmpty(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $this->client->request('GET', '/admin/estimator/partnerships');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Aucune proposition', (string) $this->client->getResponse()->getContent());
    }

    public function testListDisplaysSeededProposal(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $proposal = $this->seedProposal('Alice Partenaire', 'alice@agency.com');

        $this->client->request('GET', '/admin/estimator/partnerships');
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Alice Partenaire', $content);
        self::assertStringContainsString($proposal->id()->toRfc4122(), $content);
    }

    // ─── Détail ──────────────────────────────────────────────────────────

    public function testShowReturnsOkWithProposalData(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $proposal = $this->seedProposal('Bob Partner', 'bob@partner.com');

        $this->client->request('GET', '/admin/estimator/partnerships/'.$proposal->id()->toRfc4122());
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Bob Partner', $content);
        self::assertStringContainsString('bob@partner.com', $content);
        self::assertStringContainsString('À étudier', $content);
    }

    public function testShowReturns404ForInvalidUuid(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $this->client->request('GET', '/admin/estimator/partnerships/not-a-valid-uuid');
        self::assertResponseStatusCodeSame(404);
    }

    public function testShowReturns404ForUnknownId(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $this->client->request('GET', '/admin/estimator/partnerships/'.Uuid::v7()->toRfc4122());
        self::assertResponseStatusCodeSame(404);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function purgePartnershipTable(): void
    {
        /** @var Connection $conn */
        $conn = $this->em->getConnection();
        $conn->executeStatement('TRUNCATE TABLE estimator_partnership_proposal');
        $this->em->clear();
    }

    private function seedProposal(string $name, string $email): EstimatorPartnershipProposal
    {
        $proposal = EstimatorPartnershipProposal::create(
            id:                    Uuid::v7(),
            leadRequestId:         null,
            name:                  $name,
            email:                 $email,
            phone:                 null,
            company:               'Test Agency',
            projectType:           'businessapp',
            pricingVersion:        '2026-v1',
            estimateMinimumMinor:  250_000,
            estimateMaximumMinor:  400_000,
            currency:              'EUR',
            partnershipType:       'revenue_sharing',
            projectStage:          'idea',
            generatesRevenue:      null,
            proposalText:          'Notre proposition de partenariat test.',
            relevanceText:         null,
            questionnaireSnapshot: ['type' => 'businessapp'],
            estimateSnapshot:      ['min' => 250_000, 'max' => 400_000],
            requestId:             Uuid::v7()->toRfc4122(),
            createdAt:             new \DateTimeImmutable(),
        );
        $this->repo->save($proposal);
        $this->em->clear();

        return $proposal;
    }
}
