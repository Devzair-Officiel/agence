<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use App\Tests\Admin\Support\AdminDatabaseCleanup;
use App\Tests\Admin\Support\AdminHttpTestHelper;
use App\Tests\EditorialMedia\Support\EditorialMediaDatabaseCleanup;
use App\Tests\EditorialMedia\Support\MediaAssetBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Bout-à-bout HTTP de `/admin/media` :
 *   - firewall admin actif ;
 *   - pagination (`per_page = 20`) et clamp `page ≥ 1` ;
 *   - Cache-Control admin appliqué (aucun cache CDN) ;
 *   - état vide rendu sans planter.
 */
final class AdminMediaListControllerTest extends WebTestCase
{
    use EditorialMediaDatabaseCleanup;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        AdminDatabaseCleanup::purge($em);
        $this->clearEditorialMediaTables($em);
    }

    public function testUnauthenticatedIsRedirectedToLogin(): void
    {
        $this->client->request('GET', '/admin/media');

        self::assertResponseRedirects();
        $location = (string) $this->client->getResponse()->headers->get('Location');
        self::assertStringContainsString('/admin/login', $location);
    }

    public function testEmptyStateRendersWithoutTable(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $crawler = $this->client->request('GET', '/admin/media');

        self::assertResponseIsSuccessful();
        self::assertSame(
            0,
            $crawler->filterXPath('//table[contains(@class, "admin-table")]')->count(),
            'Aucun tableau ne doit apparaître quand la liste est vide.',
        );
        self::assertGreaterThan(
            0,
            $crawler->filterXPath('//*[contains(@class, "empty-state")]')->count(),
            'Un état vide doit être rendu.',
        );
    }

    public function testAuthenticatedRendersMediaGridWithPagination(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $this->seedMedia(22);

        $crawler = $this->client->request('GET', '/admin/media');

        self::assertResponseIsSuccessful();
        // Page 1 : 20 cartes exactement (per_page figé).
        self::assertSame(
            20,
            $crawler->filterXPath("//*[contains(concat(' ', normalize-space(@class), ' '), ' admin-media-card ')]")->count(),
        );
        // Un lien « Suivant → » doit apparaître puisqu'il reste 2 médias.
        $nextLink = $crawler->filterXPath(
            '//nav[@aria-label="Pagination des médias"]//a[contains(normalize-space(.), "Suivant")]',
        );
        self::assertSame(1, $nextLink->count(), 'Le lien Suivant doit être présent sur la page 1.');
        self::assertStringContainsString('page=2', (string) $nextLink->attr('href'));
    }

    public function testCacheControlHeadersDenyCaching(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);

        $this->client->request('GET', '/admin/media');

        $cacheControl = (string) $this->client->getResponse()->headers->get('Cache-Control');
        self::assertStringContainsString('no-store', $cacheControl);
        self::assertStringContainsString('no-cache', $cacheControl);
        self::assertStringContainsString('must-revalidate', $cacheControl);
        self::assertStringContainsString('private', $cacheControl);
    }

    public function testPageBelowOneIsClampedToOne(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $this->seedMedia(3);

        $this->client->request('GET', '/admin/media?page=-5');

        // Un `page` invalide ne doit ni jeter (RangeError sur `int<1, max>`)
        // ni renvoyer 400 : le contrôleur clampe à 1.
        self::assertResponseIsSuccessful();
    }

    public function testSecondPageServesRemainingItems(): void
    {
        AdminHttpTestHelper::createAndLogin(self::getContainer(), $this->client);
        $this->seedMedia(22);

        $crawler = $this->client->request('GET', '/admin/media?page=2');

        self::assertResponseIsSuccessful();
        self::assertSame(
            2,
            $crawler->filterXPath("//*[contains(concat(' ', normalize-space(@class), ' '), ' admin-media-card ')]")->count(),
            'La page 2 doit contenir exactement les 2 médias restants.',
        );
        self::assertSame(
            1,
            $crawler->filterXPath(
                '//nav[@aria-label="Pagination des médias"]//a[contains(normalize-space(.), "Précédent")]',
            )->count(),
            'Le lien Précédent doit être présent sur la page 2.',
        );
    }

    private function seedMedia(int $count): void
    {
        $repo = self::getContainer()->get(MediaAssetRepositoryInterface::class);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        for ($i = 1; $i <= $count; ++$i) {
            $repo->save((new MediaAssetBuilder())
                ->withOriginalFilename(\sprintf('media-%02d.jpg', $i))
                ->withCreatedAt(\sprintf('2026-08-%02dT10:%02d:00+00:00', 1 + intdiv($i, 24), $i % 60))
                ->build());
        }
        $em->flush();
        $em->clear();
    }
}
