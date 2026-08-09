<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Presentation\ConditionalCache;

use App\Editorial\Application\View\ArticleDetailView;
use App\Editorial\Application\View\ArticleSummaryView;
use App\Editorial\Application\View\PaginationView;
use App\Editorial\Application\View\PublicArticleImageView;
use App\Editorial\Presentation\Http\ConditionalCache\ArticleETag;
use App\Editorial\Presentation\Http\ConditionalCache\ArticleListETag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verrouille le contrat des helpers d'ETag :
 *
 * - la valeur retournée est un hash brut (sans `W/"…"`) — indispensable
 *   pour que `Response::setEtag($v, weak: true)` produise un entête
 *   simple `W/"<hash>"` et non une double enveloppe `W/"W/"…""` ;
 * - passer cette valeur à `setEtag(weak: true)` doit produire un entête
 *   conforme RFC 7232.
 */
#[CoversClass(ArticleETag::class)]
#[CoversClass(ArticleListETag::class)]
final class ArticleETagTest extends TestCase
{
    public function testDetailEtagIsRawSha256Hex(): void
    {
        $view = new ArticleDetailView(
            id: '019fcf03-0000-7000-8000-000000000000',
            slug: 'sample',
            title: 'Sample',
            excerpt: 'Sample excerpt suffisamment long pour dépasser la borne min.',
            bodyMarkdown: 'Body',
            contentHtml: '<p>Body</p>',
            seoTitle: 'Sample SEO title assez long pour passer',
            seoDescription: 'Description SEO assez longue pour passer les bornes.',
            authorName: 'Devzair',
            authorType: 'organization',
            expertiseIds: ['concevoir'],
            publishedAt: '2026-08-04T12:00:00+00:00',
            updatedAt: '2026-08-04T12:00:00+00:00',
        );

        $etag = ArticleETag::forDetail($view);

        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $etag);
    }

    public function testListEtagIsRawSha256Hex(): void
    {
        $item = new ArticleSummaryView(
            id: '019fcf03-0000-7000-8000-000000000001',
            slug: 'sample',
            title: 'Sample',
            excerpt: 'Un extrait',
            authorName: 'Devzair',
            authorType: 'organization',
            expertiseIds: ['concevoir'],
            publishedAt: '2026-08-04T12:00:00+00:00',
            updatedAt: '2026-08-04T12:00:00+00:00',
        );
        $pagination = new PaginationView(page: 1, perPage: 10, total: 1, totalPages: 1);

        $etag = ArticleListETag::forPage([$item], $pagination);

        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $etag);
    }

    public function testSetEtagWeakProducesSingleEnvelope(): void
    {
        $raw = ArticleETag::hashOf('any|material|v1');

        $response = new Response();
        $response->setEtag($raw, weak: true);

        $header = $response->headers->get('ETag');
        self::assertIsString($header);
        self::assertMatchesRegularExpression('/^W\/"[a-f0-9]{64}"$/', $header);
        self::assertStringNotContainsString('W/"W/', $header, 'ETag doit être en enveloppe simple, pas doublée.');
    }

    /**
     * Phase 9B — verrouille le fait qu'un bump de `CONTRACT_VERSION` produit
     * bien un hash différent, même à `id` + `updatedAt` identiques. Sans ce
     * test, une régression pourrait aligner v3 sur v2 et casser
     * silencieusement le contrat de cache HTTP côté consommateurs.
     */
    public function testDetailContractVersionIsWiredIntoHash(): void
    {
        $view = $this->sampleDetailView();

        $current = ArticleETag::forDetail($view);
        $material = \sprintf('%s|%s|%s', $view->id, $view->updatedAt, 'v2');
        $previous = ArticleETag::hashOf($material);

        self::assertSame('v3', ArticleETag::CONTRACT_VERSION);
        self::assertNotSame($previous, $current, 'v3 doit produire un hash différent de v2 sur le même matériau.');
    }

    /**
     * Phase 9B — l'ETag de détail est indexé par `updatedAt`. Quand un admin
     * modifie l'image principale, l'agrégat pousse `updatedAt` (cf.
     * `Article::changeHeroImage`), ce qui doit se répercuter sur l'ETag.
     * On simule ici le résultat côté vue : mêmes id/contrat, seul `updatedAt`
     * change → hash différent.
     */
    public function testDetailEtagChangesWhenHeroImageMutatesUpdatedAt(): void
    {
        $before = $this->sampleDetailView(heroImage: null, updatedAt: '2026-08-04T12:00:00+00:00');
        $after = $this->sampleDetailView(
            heroImage: new PublicArticleImageView(
                url: '/api/media/019fcf03-0000-7000-8000-000000000abc',
                alt: 'Ajout de vignette',
                width: 1600,
                height: 900,
                mimeType: 'image/webp',
            ),
            updatedAt: '2026-08-04T12:05:00+00:00',
        );

        self::assertNotSame(ArticleETag::forDetail($before), ArticleETag::forDetail($after));
    }

    /**
     * Phase 9B — même verrouillage côté liste : le bump `v1 → v2` doit
     * produire un hash différent sur un matériau strictement identique.
     */
    public function testListContractVersionIsWiredIntoHash(): void
    {
        $item = $this->sampleSummaryView();
        $pagination = new PaginationView(page: 1, perPage: 10, total: 1, totalPages: 1);

        $current = ArticleListETag::forPage([$item], $pagination);
        $legacyMaterial = \sprintf(
            "%d|%d|%d|%s\n%s:%s",
            $pagination->page,
            $pagination->perPage,
            $pagination->total,
            'v1',
            $item->id,
            $item->updatedAt,
        );
        $previous = ArticleETag::hashOf($legacyMaterial);

        self::assertSame('v2', ArticleListETag::CONTRACT_VERSION);
        self::assertNotSame($previous, $current, 'v2 doit produire un hash différent de v1 sur le même matériau.');
    }

    private function sampleDetailView(
        ?PublicArticleImageView $heroImage = null,
        string $updatedAt = '2026-08-04T12:00:00+00:00',
    ): ArticleDetailView {
        return new ArticleDetailView(
            id: '019fcf03-0000-7000-8000-000000000000',
            slug: 'sample',
            title: 'Sample',
            excerpt: 'Sample excerpt suffisamment long pour dépasser la borne min.',
            bodyMarkdown: 'Body',
            contentHtml: '<p>Body</p>',
            seoTitle: 'Sample SEO title assez long pour passer',
            seoDescription: 'Description SEO assez longue pour passer les bornes.',
            authorName: 'Devzair',
            authorType: 'organization',
            expertiseIds: ['concevoir'],
            publishedAt: '2026-08-04T12:00:00+00:00',
            updatedAt: $updatedAt,
            heroImage: $heroImage,
        );
    }

    private function sampleSummaryView(): ArticleSummaryView
    {
        return new ArticleSummaryView(
            id: '019fcf03-0000-7000-8000-000000000001',
            slug: 'sample',
            title: 'Sample',
            excerpt: 'Un extrait',
            authorName: 'Devzair',
            authorType: 'organization',
            expertiseIds: ['concevoir'],
            publishedAt: '2026-08-04T12:00:00+00:00',
            updatedAt: '2026-08-04T12:00:00+00:00',
        );
    }
}
