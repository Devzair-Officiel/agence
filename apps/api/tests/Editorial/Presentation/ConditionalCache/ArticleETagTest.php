<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Presentation\ConditionalCache;

use App\Editorial\Application\View\ArticleDetailView;
use App\Editorial\Application\View\ArticleSummaryView;
use App\Editorial\Application\View\PaginationView;
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
}
