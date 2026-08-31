<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http\Pricing;

use App\Estimator\Domain\Pricing\PricingConfiguration;
use App\Estimator\Domain\Pricing\PricingConfigurationRepositoryInterface;
use App\Estimator\Domain\Pricing\PricingConfigurationStatus;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use App\Tests\Admin\Support\AdminHttpTestHelper;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Tests d'intégration pour l'administration des grilles tarifaires (EST-8).
 *
 * Vérifie :
 * - Contrôle d'accès : non authentifié → redirect login
 * - Liste : renvoie 200 avec la configuration seeded
 * - Création : POST clone la publiée, crée un brouillon, redirige vers edit
 * - Édition : GET charge le brouillon
 * - Publication : atomic draft → published, ancienne publiée → archived
 * - Suppression : brouillon uniquement, draft → supprimé
 * - Protection CSRF : POST sans token → redirect avec flash error
 */
final class AdminEstimatorPricingControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private PricingConfigurationRepositoryInterface $pricingRepo;

    protected function setUp(): void
    {
        $this->client      = self::createClient();
        $this->em          = self::getContainer()->get(EntityManagerInterface::class);
        $this->pricingRepo = self::getContainer()->get(PricingConfigurationRepositoryInterface::class);
        AdminDatabaseCleanup::purge($this->em);
        $this->purgePricingTable();
    }

    // ─── Contrôle d'accès ────────────────────────────────────────────────

    public function testListRedirectsUnauthenticatedToLogin(): void
    {
        $this->client->request('GET', '/admin/pricing');
        self::assertResponseRedirects('/admin/login');
    }

    // ─── Liste ───────────────────────────────────────────────────────────

    public function testListReturnsOkWithConfigurations(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $this->seedPublishedConfig('2026-v1');

        $this->client->request('GET', '/admin/pricing');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('2026-v1', (string) $this->client->getResponse()->getContent());
    }

    // ─── Création ────────────────────────────────────────────────────────

    public function testNewGetRendersForm(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $this->seedPublishedConfig('2026-v1');

        $this->client->request('GET', '/admin/pricing/new');
        self::assertResponseIsSuccessful();
    }

    public function testNewPostWithoutCsrfReturns403(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $this->seedPublishedConfig('2026-v1');

        $this->client->request('POST', '/admin/pricing/new', ['version' => 'test-draft']);
        self::assertResponseStatusCodeSame(403);
    }

    public function testNewPostCreatesDraftCloningPublished(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $this->seedPublishedConfig('2026-v1');

        $crawler = $this->client->request('GET', '/admin/pricing/new');
        $token   = $crawler->filterXPath('//form[contains(@action,"/admin/pricing/new")]//input[@name="_csrf_token"]')->attr('value');

        $this->client->request('POST', '/admin/pricing/new', [
            '_csrf_token' => $token,
            'version'     => '2027-test',
        ]);

        self::assertResponseRedirects();
        $location = (string) $this->client->getResponse()->headers->get('Location');
        self::assertStringContainsString('/admin/pricing/', $location);
        self::assertStringContainsString('/edit', $location);

        $this->em->clear();
        $draft = $this->pricingRepo->findByVersion('2027-test');
        self::assertNotNull($draft);
        self::assertSame(PricingConfigurationStatus::Draft, $draft->status());
    }

    public function testNewPostWithDuplicateVersionShowsConflict(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $this->seedPublishedConfig('2026-v1');

        $crawler = $this->client->request('GET', '/admin/pricing/new');
        $token   = $crawler->filterXPath('//form[contains(@action,"/admin/pricing/new")]//input[@name="_csrf_token"]')->attr('value');

        $this->client->request('POST', '/admin/pricing/new', [
            '_csrf_token' => $token,
            'version'     => '2026-v1',
        ]);

        self::assertResponseStatusCodeSame(409);
    }

    // ─── Édition ─────────────────────────────────────────────────────────

    public function testEditGetRendersDraftForm(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $draft = $this->seedDraftConfig('draft-edit-test');

        $this->client->request('GET', '/admin/pricing/'.$draft->id()->toRfc4122().'/edit');
        self::assertResponseIsSuccessful();
    }

    // ─── Publication ─────────────────────────────────────────────────────

    public function testPublishRequiresPost(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $draft = $this->seedDraftConfig('draft-for-publish');

        $this->client->request('GET', '/admin/pricing/'.$draft->id()->toRfc4122().'/publish');
        self::assertResponseStatusCodeSame(405);
    }

    public function testPublishWithoutCsrfRedirectsWithoutTransition(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $draft = $this->seedDraftConfig('draft-publish-csrf');

        $this->client->request('POST', '/admin/pricing/'.$draft->id()->toRfc4122().'/publish');
        self::assertResponseRedirects();

        $this->em->clear();
        $reloaded = $this->pricingRepo->findById($draft->id());
        self::assertSame(PricingConfigurationStatus::Draft, $reloaded?->status());
    }

    public function testPublishHappyPathArchivesOldAndPublishesNew(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $old   = $this->seedPublishedConfig('old-published');
        $draft = $this->seedValidDraftConfig('new-draft');

        // Get the publish CSRF token from the edit form
        $publishToken = $this->tokenFromEditForm(
            $draft->id()->toRfc4122(),
            'publish',
        );

        $this->client->request('POST', '/admin/pricing/'.$draft->id()->toRfc4122().'/publish', [
            '_csrf_token' => $publishToken,
        ]);

        self::assertResponseRedirects('/admin/pricing');

        $this->em->clear();
        $newPublished = $this->pricingRepo->findById($draft->id());
        $oldArchived  = $this->pricingRepo->findById($old->id());

        self::assertSame(PricingConfigurationStatus::Published, $newPublished?->status());
        self::assertSame(PricingConfigurationStatus::Archived, $oldArchived?->status());
    }

    // ─── Suppression ─────────────────────────────────────────────────────

    public function testDeleteRequiresPost(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $draft = $this->seedDraftConfig('draft-delete-get');

        $this->client->request('GET', '/admin/pricing/'.$draft->id()->toRfc4122().'/delete');
        self::assertResponseStatusCodeSame(405);
    }

    public function testDeleteDraftSucceeds(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $draft = $this->seedDraftConfig('draft-to-delete');
        $id    = $draft->id()->toRfc4122();

        $token = $this->tokenFromListDeleteForm($id);

        $this->client->request('POST', '/admin/pricing/'.$id.'/delete', ['_csrf_token' => $token]);
        self::assertResponseRedirects('/admin/pricing');

        $this->em->clear();
        self::assertNull($this->pricingRepo->findByVersion('draft-to-delete'));
    }

    public function testDeletePublishedConfigIsRejected(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $published = $this->seedPublishedConfig('published-no-delete');
        // Seed a draft too so there IS a delete button on the list (for token extraction)
        $draftDecoy = $this->seedDraftConfig('decoy-draft-for-token');

        // Get a delete CSRF token for the decoy draft (the only delete form visible)
        $wrongToken = $this->tokenFromListDeleteForm($draftDecoy->id()->toRfc4122());

        // Attempt to delete the published config with an unrelated token → CSRF fail → redirect
        $this->client->request('POST', '/admin/pricing/'.$published->id()->toRfc4122().'/delete', [
            '_csrf_token' => $wrongToken,
        ]);

        self::assertResponseRedirects();

        $this->em->clear();
        self::assertNotNull($this->pricingRepo->findById($published->id()), 'Published config must not be deleted.');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function purgePricingTable(): void
    {
        /** @var Connection $conn */
        $conn = $this->em->getConnection();
        $conn->executeStatement('TRUNCATE TABLE estimator_pricing_configuration');
        $this->em->clear();
    }

    private function seedPublishedConfig(string $version): PricingConfiguration
    {
        $config = PricingConfiguration::create(
            id:            Uuid::v7(),
            version:       $version,
            currency:      'EUR',
            configuration: $this->v1Config(),
        );
        $config->publish();
        $this->pricingRepo->save($config);
        $this->em->clear();

        return $config;
    }

    private function seedDraftConfig(string $version): PricingConfiguration
    {
        $config = PricingConfiguration::create(
            id:            Uuid::v7(),
            version:       $version,
            currency:      'EUR',
            configuration: ['base_amounts' => []],
        );
        $this->pricingRepo->save($config);
        $this->em->clear();

        return $config;
    }

    private function seedValidDraftConfig(string $version): PricingConfiguration
    {
        $config = PricingConfiguration::create(
            id:            Uuid::v7(),
            version:       $version,
            currency:      'EUR',
            configuration: $this->v1Config(),
        );
        $this->pricingRepo->save($config);
        $this->em->clear();

        return $config;
    }

    /**
     * Gets a CSRF token from the publish form on the edit page.
     */
    private function tokenFromEditForm(string $id, string $actionSuffix): string
    {
        $crawler = $this->client->request('GET', '/admin/pricing/'.$id.'/edit');
        $xpath   = \sprintf(
            '//form[contains(@action, "/admin/pricing/%s/%s")]//input[@name="_csrf_token"]',
            $id,
            $actionSuffix,
        );
        $node = $crawler->filterXPath($xpath);
        \assert($node->count() > 0, \sprintf('Formulaire %s introuvable pour %s.', $actionSuffix, $id));
        $token = $node->attr('value');
        \assert(\is_string($token) && $token !== '');

        return $token;
    }

    /**
     * Gets the delete CSRF token for a given draft from the list page.
     */
    private function tokenFromListDeleteForm(string $id): string
    {
        $crawler = $this->client->request('GET', '/admin/pricing');
        $xpath   = \sprintf(
            '//form[contains(@action, "/admin/pricing/%s/delete")]//input[@name="_csrf_token"]',
            $id,
        );
        $node = $crawler->filterXPath($xpath);
        \assert($node->count() > 0, \sprintf('Bouton supprimer introuvable pour %s sur la liste.', $id));
        $token = $node->attr('value');
        \assert(\is_string($token) && $token !== '');

        return $token;
    }

    /**
     * @return array<string, mixed>
     */
    private function v1Config(): array
    {
        return [
            'base_amounts'       => ['vitrinesite' => ['min' => 90_000, 'max' => 130_000], 'ecommerce' => ['min' => 170_000, 'max' => 250_000], 'businessapp' => ['min' => 250_000, 'max' => 400_000], 'refonte' => ['min' => 70_000, 'max' => 130_000]],
            'scale_percents'     => ['small' => ['min' => 100, 'max' => 100], 'medium' => ['min' => 115, 'max' => 120], 'large' => ['min' => 130, 'max' => 145], 'unknown' => ['min' => 100, 'max' => 100]],
            'feature_complexity' => ['contact_form' => 'light', 'multilingual' => 'light', 'content_management' => 'light', 'booking_form' => 'medium', 'payment' => 'medium', 'shipping' => 'medium', 'customer_accounts' => 'medium', 'notifications' => 'medium', 'import_export' => 'medium', 'stock_management' => 'medium', 'promotions' => 'medium', 'authentication' => 'advanced', 'roles_and_permissions' => 'advanced', 'document_generation' => 'advanced', 'api_integration' => 'advanced'],
            'complexity_amounts' => ['light' => ['min' => 10_000, 'max' => 35_000], 'medium' => ['min' => 35_000, 'max' => 80_000], 'advanced' => ['min' => 70_000, 'max' => 180_000]],
            'content_amounts'    => ['visual_identity_needed' => ['min' => 50_000, 'max' => 100_000], 'visual_identity_partial' => ['min' => 25_000, 'max' => 50_000], 'content_to_write' => ['min' => 40_000, 'max' => 90_000], 'content_with_help' => ['min' => 15_000, 'max' => 40_000], 'photography_needed' => ['min' => 25_000, 'max' => 60_000]],
            'visibility_amounts' => ['seo_basic' => null, 'seo_advanced' => ['min' => 30_000, 'max' => 80_000], 'local_visibility' => ['min' => 20_000, 'max' => 50_000], 'editorial_strategy' => ['min' => 30_000, 'max' => 70_000]],
            'recurring_amounts'  => ['ESSENTIAL_MAINTENANCE' => ['min' => 4_900, 'max' => 6_900], 'MAINTENANCE_FOLLOWUP' => ['min' => 8_900, 'max' => 12_900], 'ACCOMPANIMENT' => ['min' => 14_900, 'max' => 21_900], 'REGULAR_EVOLUTION' => ['min' => 24_900, 'max' => 39_900], 'CONTINUOUS_SEO' => ['min' => 30_000, 'max' => 60_000]],
        ];
    }
}
