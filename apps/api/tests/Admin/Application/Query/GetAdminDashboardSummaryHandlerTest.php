<?php

declare(strict_types=1);

namespace App\Tests\Admin\Application\Query;

use App\Admin\Application\Query\GetAdminDashboardSummaryHandler;
use App\Editorial\Domain\ArticleStatus;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\InMemoryAdminArticleReadRepository;
use App\Tests\EditorialMedia\Support\InMemoryMediaAssetRepository;
use App\EditorialMedia\Domain\MediaAsset;
use App\EditorialMedia\Domain\MediaDimensions;
use App\EditorialMedia\Domain\MediaType;
use App\EditorialMedia\Domain\Sha256;
use App\EditorialMedia\Domain\StorageKey;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Vérifie que le handler agrège correctement les compteurs et la liste récente
 * depuis les ports existants, sans logique SQL ni infrastructure Doctrine.
 */
final class GetAdminDashboardSummaryHandlerTest extends TestCase
{
    private InMemoryAdminArticleReadRepository $articleRepo;
    private InMemoryMediaAssetRepository $mediaRepo;
    private GetAdminDashboardSummaryHandler $handler;

    protected function setUp(): void
    {
        $this->articleRepo = new InMemoryAdminArticleReadRepository();
        $this->mediaRepo   = new InMemoryMediaAssetRepository();
        $this->handler     = new GetAdminDashboardSummaryHandler($this->articleRepo, $this->mediaRepo);
    }

    public function testCountsDraftCorrectly(): void
    {
        $this->articleRepo->save((new ArticleBuilder())->withSlug('draft-a')->build());
        $this->articleRepo->save((new ArticleBuilder())->withSlug('draft-b')->build());
        $this->articleRepo->save((new ArticleBuilder())->withSlug('pub-a')->published()->build());

        $summary = ($this->handler)();

        self::assertSame(2, $summary->drafts);
    }

    public function testCountsPublishedCorrectly(): void
    {
        $this->articleRepo->save((new ArticleBuilder())->withSlug('pub-x')->published()->build());
        $this->articleRepo->save((new ArticleBuilder())->withSlug('pub-y')->published()->build());
        $this->articleRepo->save((new ArticleBuilder())->withSlug('draft-x')->build());

        $summary = ($this->handler)();

        self::assertSame(2, $summary->published);
    }

    public function testCountsArchivedCorrectly(): void
    {
        $now = new \DateTimeImmutable('2026-08-28T10:00:00+00:00');
        $article = (new ArticleBuilder())->withSlug('arch-a')->published()->withNow($now)->build();
        $article->archive($now);
        $this->articleRepo->save($article);

        $this->articleRepo->save((new ArticleBuilder())->withSlug('draft-z')->build());

        $summary = ($this->handler)();

        self::assertSame(1, $summary->archived);
    }

    public function testCountsMediaCorrectly(): void
    {
        $this->mediaRepo->save($this->makeMedia('2026-08-01T10:00:00+00:00', 'img-1.jpg'));
        $this->mediaRepo->save($this->makeMedia('2026-08-02T10:00:00+00:00', 'img-2.jpg'));
        $this->mediaRepo->save($this->makeMedia('2026-08-03T10:00:00+00:00', 'img-3.jpg'));

        $summary = ($this->handler)();

        self::assertSame(3, $summary->mediaCount);
    }

    public function testRecentArticlesLimitedToFive(): void
    {
        for ($i = 1; $i <= 8; ++$i) {
            $this->articleRepo->save(
                (new ArticleBuilder())
                    ->withSlug("article-{$i}")
                    ->withNow(new \DateTimeImmutable("2026-08-0{$i}T10:00:00+00:00"))
                    ->build()
            );
        }

        $summary = ($this->handler)();

        self::assertCount(5, $summary->recentArticles);
    }

    public function testRecentArticlesOrderedByUpdatedAtDescending(): void
    {
        $this->articleRepo->save(
            (new ArticleBuilder())->withSlug('old')->withNow(new \DateTimeImmutable('2026-08-01T10:00:00+00:00'))->build()
        );
        $this->articleRepo->save(
            (new ArticleBuilder())->withSlug('recent')->withNow(new \DateTimeImmutable('2026-08-10T10:00:00+00:00'))->build()
        );
        $this->articleRepo->save(
            (new ArticleBuilder())->withSlug('middle')->withNow(new \DateTimeImmutable('2026-08-05T10:00:00+00:00'))->build()
        );

        $summary = ($this->handler)();

        self::assertSame('recent', $summary->recentArticles[0]->slug);
        self::assertSame('middle', $summary->recentArticles[1]->slug);
        self::assertSame('old',    $summary->recentArticles[2]->slug);
    }

    public function testBehaviourWithEmptyDatabase(): void
    {
        $summary = ($this->handler)();

        self::assertSame(0, $summary->published);
        self::assertSame(0, $summary->drafts);
        self::assertSame(0, $summary->archived);
        self::assertSame(0, $summary->mediaCount);
        self::assertSame([], $summary->recentArticles);
    }

    private function makeMedia(string $createdAt, string $filename): MediaAsset
    {
        $id = Uuid::v7();

        return MediaAsset::record(
            id: $id,
            storageKey: StorageKey::forNewAsset($id, MediaType::Jpeg),
            originalFilename: $filename,
            mimeType: MediaType::Jpeg,
            sizeBytes: 1024,
            dimensions: MediaDimensions::of(1200, 800),
            sha256: Sha256::fromString(str_repeat('a', 64)),
            now: new \DateTimeImmutable($createdAt),
        );
    }
}
