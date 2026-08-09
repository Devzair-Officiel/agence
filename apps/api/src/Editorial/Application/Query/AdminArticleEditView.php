<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Domain\Article;
use App\Editorial\Domain\ArticleStatus;
use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\ExpertiseIdentifier;

/**
 * Vue plate d'un article pour l'écran d'édition administrateur.
 *
 * Distinct de `ArticleDetailView` (public) :
 *   - inclut le statut et les dates brutes — `updatedAt` sert d'ETag logique
 *     à l'édition, `createdAt` documente l'historique dans la prévisualisation
 *     admin (Phase 8C4) ;
 *   - n'embarque pas le rendu HTML — l'admin travaille sur la source Markdown ;
 *     la prévisualisation (Phase 8C4) enveloppe cette vue dans
 *     `AdminArticlePreviewView` en y ajoutant `contentHtml` calculé par
 *     `CommonMarkArticleRenderer` ;
 *   - ne franchit pas la frontière `/api/**` : c'est un DTO de présentation
 *     interne.
 */
final class AdminArticleEditView
{
    /**
     * @param list<ExpertiseIdentifier> $expertises
     */
    public function __construct(
        public readonly string $id,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $excerpt,
        public readonly string $bodyMarkdown,
        public readonly string $seoTitle,
        public readonly string $seoDescription,
        public readonly string $authorName,
        public readonly AuthorType $authorType,
        public readonly array $expertises,
        public readonly ArticleStatus $status,
        public readonly ?\DateTimeImmutable $publishedAt,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
        public readonly ?AdminArticleHeroImageView $heroImage = null,
    ) {
    }

    /**
     * Reconstitue la vue plate à partir de l'agrégat Doctrine hydraté.
     *
     * Le descripteur média est fourni par l'appelant (adaptateur admin qui
     * a orchestré le JOIN cross-context) : l'agrégat `Article` ne connaît
     * qu'un `Uuid` de média et un alt text — le rendu admin a besoin des
     * dimensions et du MIME. Passer `null` produit une vue sans image
     * principale, cohérent avec un article Draft/Published sans hero.
     *
     * Si l'article porte une référence média mais que le descripteur n'a
     * pas pu être résolu (rare : race entre lecture Article/lecture Media),
     * on retourne une vue SANS image — l'admin verra alors le CTA « choisir
     * une image » plutôt qu'un rendu cassé. Le read-repository loggue cette
     * situation le cas échéant.
     */
    public static function fromEntity(Article $article, ?\App\Editorial\Application\Media\MediaAssetDescriptor $heroDescriptor = null): self
    {
        $seo = $article->seo();
        $author = $article->author();

        $heroImageVo = $article->heroImage();
        $heroImageView = null;
        if ($heroImageVo !== null && $heroDescriptor !== null && $heroImageVo->mediaAssetId()->equals($heroDescriptor->id)) {
            $heroImageView = new AdminArticleHeroImageView(
                mediaId: $heroDescriptor->id->toRfc4122(),
                altText: $heroImageVo->altText(),
                width: $heroDescriptor->width,
                height: $heroDescriptor->height,
                mimeType: $heroDescriptor->mimeType,
            );
        }

        return new self(
            id: $article->id()->toRfc4122(),
            slug: $article->slug()->value(),
            title: $article->title(),
            excerpt: $article->excerpt(),
            bodyMarkdown: $article->bodyMarkdown(),
            seoTitle: $seo->title(),
            seoDescription: $seo->description(),
            authorName: $author->name(),
            authorType: $author->type(),
            expertises: $article->expertises(),
            status: $article->status(),
            publishedAt: $article->publishedAt(),
            createdAt: $article->createdAt(),
            updatedAt: $article->updatedAt(),
            heroImage: $heroImageView,
        );
    }
}
