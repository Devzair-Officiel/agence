<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application\Command;

use App\Editorial\Application\Command\RemoveDraftArticleHeroImage;
use App\Editorial\Application\Command\RemoveDraftArticleHeroImageHandler;
use App\Editorial\Domain\ArticleHeroImage;
use App\Editorial\Domain\Exception\ArticleNotEditableException;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\EntityManagerStub;
use App\Tests\Editorial\Support\FixedClock;
use App\Tests\Editorial\Support\InMemoryArticleRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class RemoveDraftArticleHeroImageHandlerTest extends TestCase
{
    use EntityManagerStub;

    public function testRemovesHeroImageAndFlushesOnce(): void
    {
        $articleId = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $article = (new ArticleBuilder())
            ->withId($articleId)
            ->withSlug('a-vider')
            ->withNow(new \DateTimeImmutable('2026-08-01T09:00:00+00:00'))
            ->build();
        $article->changeHeroImage(
            ArticleHeroImage::create(Uuid::v7(), 'Alt existant'),
            new \DateTimeImmutable('2026-08-02T09:00:00+00:00'),
        );
        $repository->save($article);

        $handler = new RemoveDraftArticleHeroImageHandler(
            $repository,
            $this->entityManagerExpectingFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
        );

        $result = $handler(new RemoveDraftArticleHeroImage($articleId));

        self::assertTrue($result->mutated);
        self::assertNull($result->article->heroImage());
    }

    public function testReturnsUnchangedWhenNoHeroImage(): void
    {
        $articleId = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $article = (new ArticleBuilder())
            ->withId($articleId)
            ->withSlug('sans-hero')
            ->withNow(new \DateTimeImmutable('2026-08-01T09:00:00+00:00'))
            ->build();
        $repository->save($article);
        $before = $article->updatedAt();

        $handler = new RemoveDraftArticleHeroImageHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
        );

        $result = $handler(new RemoveDraftArticleHeroImage($articleId));

        self::assertFalse($result->mutated);
        self::assertSame($before->getTimestamp(), $result->article->updatedAt()->getTimestamp());
    }

    public function testRejectsWhenArticleNotFound(): void
    {
        $handler = new RemoveDraftArticleHeroImageHandler(
            new InMemoryArticleRepository(),
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
        );

        $this->expectException(ArticleNotFoundException::class);

        $handler(new RemoveDraftArticleHeroImage(Uuid::v7()));
    }

    public function testRejectsWhenArticleIsPublished(): void
    {
        $articleId = Uuid::v7();
        $repository = new InMemoryArticleRepository();
        $repository->save(
            (new ArticleBuilder())
                ->withId($articleId)
                ->withSlug('publie-non-effacable')
                ->withNow(new \DateTimeImmutable('2026-08-01T09:00:00+00:00'))
                ->published()
                ->build(),
        );

        $handler = new RemoveDraftArticleHeroImageHandler(
            $repository,
            $this->entityManagerExpectingNoFlush(),
            new FixedClock('2026-08-05T10:00:00+00:00'),
        );

        $this->expectException(ArticleNotEditableException::class);

        $handler(new RemoveDraftArticleHeroImage($articleId));
    }
}
