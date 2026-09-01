<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http\Estimator;

use App\Estimator\Domain\Lead\EstimatorLead;
use App\Estimator\Domain\Lead\EstimatorLeadRepositoryInterface;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use App\Tests\Admin\Support\AdminHttpTestHelper;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Tests d'intégration pour l'administration des leads estimateur (EST-9).
 *
 * Vérifie :
 * - Contrôle d'accès : non authentifié → redirect login
 * - Liste : renvoie 200 avec le lead seeded
 * - Détail : renvoie 200 avec les données du lead
 * - Détail : UUID invalide → 404
 * - Détail : UUID inconnu → 404
 */
final class AdminEstimatorLeadControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private EstimatorLeadRepositoryInterface $repo;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em     = self::getContainer()->get(EntityManagerInterface::class);
        $this->repo   = self::getContainer()->get(EstimatorLeadRepositoryInterface::class);
        AdminDatabaseCleanup::purge($this->em);
        $this->purgeLeadTable();
    }

    // ─── Contrôle d'accès ────────────────────────────────────────────────

    public function testListRedirectsUnauthenticatedToLogin(): void
    {
        $this->client->request('GET', '/admin/estimator/leads');
        self::assertResponseRedirects('/admin/login');
    }

    public function testShowRedirectsUnauthenticatedToLogin(): void
    {
        $this->client->request('GET', '/admin/estimator/leads/'.Uuid::v7()->toRfc4122());
        self::assertResponseRedirects('/admin/login');
    }

    // ─── Liste ───────────────────────────────────────────────────────────

    public function testListReturnsOkWhenEmpty(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $this->client->request('GET', '/admin/estimator/leads');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Aucune demande reçue pour le moment', (string) $this->client->getResponse()->getContent());
    }

    public function testListDisplaysSeededLead(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $lead = $this->seedLead('Jean Dupont', 'jean@example.com');

        $this->client->request('GET', '/admin/estimator/leads');
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Jean Dupont', $content);
        self::assertStringContainsString($lead->id()->toRfc4122(), $content);
    }

    // ─── Détail ──────────────────────────────────────────────────────────

    public function testShowReturnsOkWithLeadData(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $lead = $this->seedLead('Marie Martin', 'marie@example.com');

        $this->client->request('GET', '/admin/estimator/leads/'.$lead->id()->toRfc4122());
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Marie Martin', $content);
        self::assertStringContainsString('marie@example.com', $content);
    }

    public function testShowReturns404ForInvalidUuid(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $this->client->request('GET', '/admin/estimator/leads/not-a-valid-uuid');
        self::assertResponseStatusCodeSame(404);
    }

    public function testShowReturns404ForUnknownId(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $this->client->request('GET', '/admin/estimator/leads/'.Uuid::v7()->toRfc4122());
        self::assertResponseStatusCodeSame(404);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function purgeLeadTable(): void
    {
        /** @var Connection $conn */
        $conn = $this->em->getConnection();
        $conn->executeStatement('TRUNCATE TABLE estimator_lead');
        $this->em->clear();
    }

    private function seedLead(string $name, string $email): EstimatorLead
    {
        $lead = EstimatorLead::create(
            id:                    Uuid::v7(),
            name:                  $name,
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
            requestId:             Uuid::v7()->toRfc4122(),
            createdAt:             new \DateTimeImmutable(),
        );
        $this->repo->save($lead);
        $this->em->clear();

        return $lead;
    }
}
