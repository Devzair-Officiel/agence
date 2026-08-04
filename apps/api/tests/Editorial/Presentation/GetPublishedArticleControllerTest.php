<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Presentation;

use App\Editorial\Infrastructure\Persistence\DoctrineArticleRepository;
use App\Editorial\Presentation\Http\GetPublishedArticleController;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EditorialDatabaseCleanup;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(GetPublishedArticleController::class)]
final class GetPublishedArticleControllerTest extends WebTestCase
{
    use EditorialDatabaseCleanup;

    public function testReturns200WithDetailPayloadForPublishedSlug(): void
    {
        $client = self::createClient();
        $this->resetDatabase();

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $repo = self::getContainer()->get(DoctrineArticleRepository::class);
        $repo->save((new ArticleBuilder())
            ->withSlug('article-detail-teste')
            ->published()
            ->build());
        $em->flush();

        $client->request('GET', '/resources/article-detail-teste');

        self::assertResponseStatusCodeSame(200);
        $data = $this->decode($client);
        self::assertSame('article-detail-teste', $data['slug']);
        self::assertArrayHasKey('body_markdown', $data);
        self::assertArrayHasKey('seo', $data);
        self::assertArrayHasKey('title', $data['seo']);
        self::assertArrayHasKey('description', $data['seo']);
        // Symfony trie les directives alphabétiquement dans HeaderBag —
        // c'est la représentation canonique retournée sur le fil.
        self::assertSame(
            'max-age=60, public, s-maxage=300',
            $client->getResponse()->headers->get('Cache-Control'),
        );
    }

    public function testReturns404ForUnknownSlug(): void
    {
        $client = self::createClient();
        $this->resetDatabase();

        $client->request('GET', '/resources/aucun-article-ici');

        self::assertResponseStatusCodeSame(404);
        $data = $this->decode($client);
        self::assertSame('not_found', $data['code']);
    }

    public function testReturns404ForDraftSlug(): void
    {
        $client = self::createClient();
        $this->resetDatabase();

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $repo = self::getContainer()->get(DoctrineArticleRepository::class);
        $repo->save((new ArticleBuilder())
            ->withSlug('brouillon-non-expose')
            ->build());
        $em->flush();

        $client->request('GET', '/resources/brouillon-non-expose');

        self::assertResponseStatusCodeSame(404);
    }

    public function testReturns400ForMalformedSlug(): void
    {
        $client = self::createClient();

        // Slug court invalide (2 lettres) mais matché par la regex de route :
        // laisse passer, puis rejeté par le VO ArticleSlug → 400.
        $client->request('GET', '/resources/ab');

        self::assertResponseStatusCodeSame(400);
        $data = $this->decode($client);
        self::assertSame('validation_error', $data['code']);
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(KernelBrowser $client): array
    {
        /** @var array<string, mixed> $data */
        $data = json_decode($client->getResponse()->getContent() ?: '{}', true, flags: JSON_THROW_ON_ERROR);

        return $data;
    }

    private function resetDatabase(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->clearEditorialTables($em);
    }
}
