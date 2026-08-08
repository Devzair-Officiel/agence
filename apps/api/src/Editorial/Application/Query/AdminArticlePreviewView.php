<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\ExpertiseIdentifier;

/**
 * Vue plate d'un article pour la prévisualisation administrateur (Phase 8C4).
 *
 * Distincte de `AdminArticleEditView` (formulaire d'édition) et de
 * `ArticleDetailView` (rendu public) :
 *   - inclut `contentHtml` (rendu CommonMark sanitizé par `MarkdownSecurityPolicy`)
 *     pour éviter que le template Twig ait à raw-render du Markdown ou à faire
 *     appel à un service applicatif — la vue est prête à consommer ;
 *   - n'expose pas `bodyMarkdown` (déjà rendu, inutile en prévisualisation) ;
 *   - inclut toutes les métadonnées SEO et l'historique (createdAt / updatedAt)
 *     — c'est précisément ce qu'un rédacteur veut auditer avant de publier ;
 *   - ne franchit pas la frontière `/api/**` : DTO de présentation interne
 *     admin uniquement.
 *
 * Immutable — aucun setter, tous les champs `readonly`.
 */
final class AdminArticlePreviewView
{
    /**
     * @param list<ExpertiseIdentifier> $expertises
     */
    public function __construct(
        public readonly string $id,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $excerpt,
        public readonly string $contentHtml,
        public readonly string $seoTitle,
        public readonly string $seoDescription,
        public readonly string $authorName,
        public readonly AuthorType $authorType,
        public readonly array $expertises,
        public readonly ArticleStatus $status,
        public readonly ?\DateTimeImmutable $publishedAt,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * Enrichit une vue d'édition existante d'un `contentHtml` sécurisé.
     * Le handler `GetAdminArticlePreviewHandler` est le seul appelant légitime :
     * il charge l'article une fois via `AdminArticleReadRepositoryInterface`
     * puis délègue le rendu Markdown à `CommonMarkArticleRenderer`.
     */
    public static function fromEditView(AdminArticleEditView $edit, string $contentHtml): self
    {
        return new self(
            id: $edit->id,
            slug: $edit->slug,
            title: $edit->title,
            excerpt: $edit->excerpt,
            contentHtml: $contentHtml,
            seoTitle: $edit->seoTitle,
            seoDescription: $edit->seoDescription,
            authorName: $edit->authorName,
            authorType: $edit->authorType,
            expertises: $edit->expertises,
            status: $edit->status,
            publishedAt: $edit->publishedAt,
            createdAt: $edit->createdAt,
            updatedAt: $edit->updatedAt,
        );
    }
}
