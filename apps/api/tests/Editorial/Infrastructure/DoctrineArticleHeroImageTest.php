<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Infrastructure;

use App\Editorial\Domain\ArticleHeroImage;
use App\Editorial\Infrastructure\Persistence\DoctrineArticleRepository;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaDimensions;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\Sha256;
use App\EditorialMedia\Domain\StorageKey;
use App\EditorialMedia\Infrastructure\Persistence\DoctrineMediaAssetRepository;
use App\Editorial\Application\Media\MediaAssetLookupInterface;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Test d'intégration Phase 9B — persistance croisée `Article` ↔ `MediaAsset`.
 *
 * Vérifie :
 *   - qu'un article Draft peut porter un `hero_media_id` valide et le recharger ;
 *   - qu'un même média peut être partagé par plusieurs articles ;
 *   - que la CHECK constraint DB refuse un état incohérent (une seule des
 *     deux colonnes non nulle) — pilotée par un SQL brut pour bypass le
 *     domaine, la CHECK étant précisément la défense en profondeur ;
 *   - que la DB accepte volontairement un `hero_media_id` orphelin (pas
 *     de FK inter-contextes — isolation DDD entre `Editorial` et
 *     `EditorialMedia`). L'intégrité est portée au niveau Application par
 *     `MediaAssetLookupInterface::exists($uuid)` avant mutation.
 */
final class DoctrineArticleHeroImageTest extends KernelTestCase
{
    use EditorialDatabaseCleanup;

    private EntityManagerInterface $entityManager;
    private DoctrineArticleRepository $articleRepository;
    private DoctrineMediaAssetRepository $mediaRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->articleRepository = $container->get(DoctrineArticleRepository::class);
        $this->mediaRepository = $container->get(DoctrineMediaAssetRepository::class);

        $this->clearEditorialTables($this->entityManager);
    }

    protected function tearDown(): void
    {
        $this->clearEditorialTables($this->entityManager);
        $this->entityManager->close();
    }

    public function testDraftRoundTripWithHeroImage(): void
    {
        $asset = $this->makeAsset('photo-hero.webp');
        $this->mediaRepository->save($asset);

        $article = (new ArticleBuilder())
            ->withSlug('article-avec-image')
            ->withNow(new \DateTimeImmutable('2026-08-08T09:00:00+00:00'))
            ->build();
        $article->changeHeroImage(
            ArticleHeroImage::create($asset->id(), 'Équipe collaborant devant un écran.'),
            new \DateTimeImmutable('2026-08-08T09:05:00+00:00'),
        );
        $this->articleRepository->save($article);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $found = $this->articleRepository->findById($article->id());
        self::assertNotNull($found);
        $hero = $found->heroImage();
        self::assertNotNull($hero);
        self::assertTrue($asset->id()->equals($hero->mediaAssetId()));
        self::assertSame('Équipe collaborant devant un écran.', $hero->altText());
    }

    public function testHeroImageIsNullByDefault(): void
    {
        $article = (new ArticleBuilder())
            ->withSlug('article-sans-image')
            ->build();
        $this->articleRepository->save($article);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $found = $this->articleRepository->findById($article->id());
        self::assertNotNull($found);
        self::assertNull($found->heroImage());
    }

    public function testDatabaseAcceptsOrphanReferenceButLookupRejectsIt(): void
    {
        // Contrat DDD : la base ACCEPTE volontairement un `hero_media_id`
        // orphelin — l'absence de FK inter-contextes est un choix explicite
        // pour préserver l'isolation entre `Editorial` et `EditorialMedia`
        // (Doctrine ORM ne peut pas représenter une FK sur colonne scalaire
        // sans introduire une association ORM qui coupleait les deux
        // agrégats). L'intégrité inter-contextes est portée en amont par
        // `MediaAssetLookupInterface::exists($uuid)`, invoqué par
        // `SetDraftArticleHeroImageHandler` avant toute mutation.
        $orphanId = Uuid::v7();
        $article = (new ArticleBuilder())
            ->withSlug('media-orphelin')
            ->build();
        $article->changeHeroImage(
            ArticleHeroImage::create($orphanId, 'Alt valide.'),
            new \DateTimeImmutable('2026-08-08T09:05:00+00:00'),
        );
        $this->articleRepository->save($article);

        // Le flush passe : la base n'a pas de FK pour bloquer.
        $this->entityManager->flush();

        // Mais la porte applicative aurait refusé l'opération en amont.
        $lookup = self::getContainer()->get(MediaAssetLookupInterface::class);
        self::assertFalse(
            $lookup->exists($orphanId),
            'MediaAssetLookup DOIT signaler l\'orphelin — c\'est la seule ligne de défense.',
        );
    }

    public function testSameMediaCanBeSharedBetweenArticles(): void
    {
        $asset = $this->makeAsset('shared.webp');
        $this->mediaRepository->save($asset);

        $a = (new ArticleBuilder())->withSlug('article-partage-a')->build();
        $b = (new ArticleBuilder())->withSlug('article-partage-b')->build();

        $now = new \DateTimeImmutable('2026-08-08T09:05:00+00:00');
        $a->changeHeroImage(ArticleHeroImage::create($asset->id(), 'Alt A'), $now);
        $b->changeHeroImage(ArticleHeroImage::create($asset->id(), 'Alt B'), $now);

        $this->articleRepository->save($a);
        $this->articleRepository->save($b);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $foundA = $this->articleRepository->findById($a->id());
        $foundB = $this->articleRepository->findById($b->id());
        self::assertNotNull($foundA);
        self::assertNotNull($foundB);
        self::assertTrue($asset->id()->equals($foundA->heroImage()?->mediaAssetId()));
        self::assertTrue($asset->id()->equals($foundB->heroImage()?->mediaAssetId()));
        self::assertSame('Alt A', $foundA->heroImage()?->altText());
        self::assertSame('Alt B', $foundB->heroImage()?->altText());
    }

    public function testCheckConstraintRefusesInconsistentPair(): void
    {
        $article = (new ArticleBuilder())
            ->withSlug('violation-check')
            ->build();
        $this->articleRepository->save($article);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $asset = $this->makeAsset('check-violation.webp');
        $this->mediaRepository->save($asset);
        $this->entityManager->flush();

        $this->expectException(\Doctrine\DBAL\Exception::class);
        $this->expectExceptionMessageMatches('/check_editorial_article_hero_image_pair/i');

        // Bypass volontaire du domaine : on écrit un état incohérent
        // directement en SQL — c'est précisément le rôle de la CHECK
        // constraint DB (défense en profondeur).
        $this->entityManager->getConnection()->executeStatement(
            'UPDATE editorial_article SET hero_media_id = :media WHERE id = :id',
            ['media' => $asset->id()->toRfc4122(), 'id' => $article->id()->toRfc4122()],
        );
    }

    private function makeAsset(string $filename): MediaAsset
    {
        $id = Uuid::v7();

        return MediaAsset::record(
            id: $id,
            storageKey: StorageKey::forNewAsset($id, MediaType::WebP),
            originalFilename: $filename,
            mimeType: MediaType::WebP,
            sizeBytes: 240_000,
            dimensions: MediaDimensions::of(1600, 900),
            sha256: Sha256::fromString(str_repeat('b', 64)),
            now: new \DateTimeImmutable('2026-08-08T09:00:00+00:00'),
        );
    }
}
