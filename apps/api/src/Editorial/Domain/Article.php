<?php

declare(strict_types=1);

namespace App\Editorial\Domain;

use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Agrégat racine du domaine éditorial.
 *
 * Contraintes fondamentales :
 * - Statut `Published` implique `publishedAt` non nul. La réciproque n'est
 *   pas imposée : un article archivé conserve sa date historique de
 *   publication (statut `Archived`, `publishedAt` non nul reste valide).
 * - Publication toujours dans le passé ou dans l'instant présent (jamais dans le futur).
 * - Slug unique — contrainte UNIQUE en base, appliquée par migration.
 * - Le contenu markdown est stocké tel quel ; le rendu HTML est délégué au front (Phase 8B).
 *
 * Les attributs Doctrine restent purement techniques : ils ne portent aucune
 * règle métier (pas de validators annotés, pas de callbacks). Toute règle
 * passe par les factories `createDraft()` / `publish()` / `archive()`.
 */
#[ORM\Entity]
#[ORM\Table(name: 'editorial_article')]
#[ORM\Index(name: 'idx_editorial_article_status_published_at', columns: ['status', 'published_at'])]
#[ORM\UniqueConstraint(name: 'uniq_editorial_article_slug', columns: ['slug'])]
class Article
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(name: 'slug', type: Types::STRING, length: 120, unique: true)]
    private string $slug;

    #[ORM\Column(name: 'title', type: Types::STRING, length: 200)]
    private string $title;

    #[ORM\Column(name: 'excerpt', type: Types::STRING, length: 320)]
    private string $excerpt;

    #[ORM\Column(name: 'body_markdown', type: Types::TEXT)]
    private string $bodyMarkdown;

    #[ORM\Column(name: 'seo_title', type: Types::STRING, length: 70)]
    private string $seoTitle;

    #[ORM\Column(name: 'seo_description', type: Types::STRING, length: 160)]
    private string $seoDescription;

    #[ORM\Column(name: 'author_name', type: Types::STRING, length: 120)]
    private string $authorName;

    #[ORM\Column(name: 'author_type', type: Types::STRING, length: 20, enumType: AuthorType::class)]
    private AuthorType $authorType;

    #[ORM\Column(name: 'status', type: Types::STRING, length: 20, enumType: ArticleStatus::class)]
    private ArticleStatus $status;

    /**
     * Liste ordonnée d'identifiants de piliers, stockée en JSONB.
     *
     * @var list<string>
     */
    #[ORM\Column(name: 'expertise_ids', type: Types::JSON, options: ['jsonb' => true])]
    private array $expertiseIds;

    #[ORM\Column(name: 'published_at', type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt;

    #[ORM\Column(name: 'created_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    /**
     * @param list<ExpertiseIdentifier> $expertises
     */
    private function __construct(
        Uuid $id,
        ArticleSlug $slug,
        string $title,
        string $excerpt,
        string $bodyMarkdown,
        SeoMetadata $seo,
        Author $author,
        ArticleStatus $status,
        array $expertises,
        \DateTimeImmutable $now,
        ?\DateTimeImmutable $publishedAt = null,
    ) {
        $trimmedTitle = trim($title);
        $trimmedExcerpt = trim($excerpt);
        $trimmedBody = trim($bodyMarkdown);

        self::assertLength('titre', $trimmedTitle, 5, 200);
        self::assertLength('résumé', $trimmedExcerpt, 40, 320);
        if ($trimmedBody === '') {
            throw new ArticleInvariantViolation('Le corps markdown ne peut pas être vide.');
        }
        if ($expertises === []) {
            throw new ArticleInvariantViolation('Au moins un pilier d\'expertise est requis.');
        }

        $this->id = $id;
        $this->slug = $slug->value();
        $this->title = $trimmedTitle;
        $this->excerpt = $trimmedExcerpt;
        $this->bodyMarkdown = $trimmedBody;
        $this->seoTitle = $seo->title();
        $this->seoDescription = $seo->description();
        $this->authorName = $author->name();
        $this->authorType = $author->type();
        $this->status = $status;
        $this->expertiseIds = ExpertiseIdentifier::toList(self::dedupe($expertises));
        $this->publishedAt = $publishedAt;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    /**
     * @param list<ExpertiseIdentifier> $expertises
     */
    public static function createDraft(
        Uuid $id,
        ArticleSlug $slug,
        string $title,
        string $excerpt,
        string $bodyMarkdown,
        SeoMetadata $seo,
        Author $author,
        array $expertises,
        \DateTimeImmutable $now,
    ): self {
        return new self(
            $id,
            $slug,
            $title,
            $excerpt,
            $bodyMarkdown,
            $seo,
            $author,
            ArticleStatus::Draft,
            $expertises,
            $now,
        );
    }

    /**
     * Publie l'article à la date `$publishedAt`, en horodatant `updatedAt`
     * avec `$now`. `$now` doit venir d'un `ClockInterface`. L'entité refuse
     * toute date de publication strictement supérieure à `$now` — cette
     * défense en profondeur double le contrôle fait côté application
     * (`PublishArticleBySlugHandler`) et protège contre un clock skew ou un
     * bug d'appelant.
     *
     * Idempotent : renvoie silencieusement si l'article est déjà publié.
     */
    public function publish(\DateTimeImmutable $publishedAt, \DateTimeImmutable $now): void
    {
        if ($this->status === ArticleStatus::Published) {
            return;
        }

        if ($publishedAt > $now) {
            throw new ArticleInvariantViolation(
                'La date de publication ne peut pas être postérieure à l\'instant présent.',
            );
        }

        $this->status = ArticleStatus::Published;
        $this->publishedAt = $publishedAt;
        $this->updatedAt = $now;
    }

    public function archive(\DateTimeImmutable $now): void
    {
        if ($this->status === ArticleStatus::Archived) {
            return;
        }

        $this->status = ArticleStatus::Archived;
        $this->updatedAt = $now;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function slug(): ArticleSlug
    {
        return ArticleSlug::fromString($this->slug);
    }

    public function title(): string
    {
        return $this->title;
    }

    public function excerpt(): string
    {
        return $this->excerpt;
    }

    public function bodyMarkdown(): string
    {
        return $this->bodyMarkdown;
    }

    public function seo(): SeoMetadata
    {
        return SeoMetadata::create($this->seoTitle, $this->seoDescription);
    }

    public function author(): Author
    {
        return match ($this->authorType) {
            AuthorType::Organization => Author::organization($this->authorName),
            AuthorType::Person => Author::person($this->authorName),
        };
    }

    public function status(): ArticleStatus
    {
        return $this->status;
    }

    /**
     * @return list<ExpertiseIdentifier>
     */
    public function expertises(): array
    {
        return ExpertiseIdentifier::fromList($this->expertiseIds);
    }

    public function publishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param list<ExpertiseIdentifier> $expertises
     *
     * @return list<ExpertiseIdentifier>
     */
    private static function dedupe(array $expertises): array
    {
        $seen = [];
        $unique = [];
        foreach ($expertises as $exp) {
            if (isset($seen[$exp->value])) {
                continue;
            }
            $seen[$exp->value] = true;
            $unique[] = $exp;
        }

        return $unique;
    }

    private static function assertLength(string $field, string $value, int $min, int $max): void
    {
        $length = mb_strlen($value);
        if ($length < $min || $length > $max) {
            throw new ArticleInvariantViolation(\sprintf(
                'Le %s doit contenir entre %d et %d caractères (actuel : %d).',
                $field,
                $min,
                $max,
                $length,
            ));
        }
    }
}
