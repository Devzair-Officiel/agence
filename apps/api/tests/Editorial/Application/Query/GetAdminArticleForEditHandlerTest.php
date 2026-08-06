<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Application\Query;

use App\Editorial\Application\Query\GetAdminArticleForEdit;
use App\Editorial\Application\Query\GetAdminArticleForEditHandler;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\Tests\Editorial\Support\ArticleBuilder;
use App\Tests\Editorial\Support\InMemoryAdminArticleReadRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class GetAdminArticleForEditHandlerTest extends TestCase
{
    public function testReturnsPlainViewForExistingArticle(): void
    {
        $id = Uuid::v7();
        $repo = new InMemoryAdminArticleReadRepository();
        $repo->save((new ArticleBuilder())->withId($id)->withSlug('vue-edition')->build());

        $handler = new GetAdminArticleForEditHandler($repo);
        $view = $handler(new GetAdminArticleForEdit($id));

        self::assertSame($id->toRfc4122(), $view->id);
        self::assertSame('vue-edition', $view->slug);
    }

    public function testRaisesNotFoundForUnknownId(): void
    {
        $handler = new GetAdminArticleForEditHandler(new InMemoryAdminArticleReadRepository());

        $this->expectException(ArticleNotFoundException::class);

        $handler(new GetAdminArticleForEdit(Uuid::v7()));
    }
}
