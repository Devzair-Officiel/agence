<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Support;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\Author;
use App\Editorial\Domain\ExpertiseIdentifier;
use App\Editorial\Domain\SeoMetadata;
use Symfony\Component\Uid\Uuid;

/**
 * Builder d'articles pour les tests. Chaque `with*` renvoie un nouveau
 * builder — pattern immutable pour éviter les surprises entre tests
 * partageant un même seed.
 */
final class ArticleBuilder
{
    private Uuid $id;

    /** @var non-empty-string */
    private string $slug = 'article-de-reference-editorial';

    /** @var non-empty-string */
    private string $title = 'Comment structurer un projet éditorial durable';

    /** @var non-empty-string */
    private string $excerpt = 'Un aperçu synthétique — assez long pour dépasser la borne minimale imposée.';

    /** @var non-empty-string */
    private string $body = "## Introduction\n\nCorps markdown de test — assez long pour être crédible.";

    /** @var non-empty-string */
    private string $seoTitle = 'Structurer un projet éditorial durable pour son agence';

    /** @var non-empty-string */
    private string $seoDescription = 'Guide court sur la structuration éditoriale : gouvernance, workflow, mesure. Adapté à une agence de taille humaine.';

    private Author $author;

    /** @var list<ExpertiseIdentifier> */
    private array $expertises = [ExpertiseIdentifier::Concevoir];

    private \DateTimeImmutable $now;

    private bool $publish = false;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->author = Author::organization('Devzair');
        $this->now = new \DateTimeImmutable('2026-08-03T10:00:00+00:00');
    }

    public function withId(Uuid $id): self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    public function withSlug(string $slug): self
    {
        $clone = clone $this;
        \assert($slug !== '');
        $clone->slug = $slug;

        return $clone;
    }

    public function withTitle(string $title): self
    {
        $clone = clone $this;
        \assert($title !== '');
        $clone->title = $title;

        return $clone;
    }

    /**
     * @param list<ExpertiseIdentifier> $expertises
     */
    public function withExpertises(array $expertises): self
    {
        $clone = clone $this;
        $clone->expertises = $expertises;

        return $clone;
    }

    public function withAuthor(Author $author): self
    {
        $clone = clone $this;
        $clone->author = $author;

        return $clone;
    }

    public function withNow(\DateTimeImmutable $now): self
    {
        $clone = clone $this;
        $clone->now = $now;

        return $clone;
    }

    public function published(): self
    {
        $clone = clone $this;
        $clone->publish = true;

        return $clone;
    }

    public function build(): Article
    {
        $article = Article::createDraft(
            $this->id,
            ArticleSlug::fromString($this->slug),
            $this->title,
            $this->excerpt,
            $this->body,
            SeoMetadata::create($this->seoTitle, $this->seoDescription),
            $this->author,
            $this->expertises,
            $this->now,
        );

        if ($this->publish) {
            $article->publish($this->now, $this->now);
        }

        return $article;
    }
}
