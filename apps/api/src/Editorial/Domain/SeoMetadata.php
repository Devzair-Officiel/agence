<?php

declare(strict_types=1);

namespace App\Editorial\Domain;

use App\Editorial\Domain\Exception\ArticleInvariantViolation;

/**
 * Métadonnées SEO d'un article.
 *
 * Les longueurs cibles respectent les recommandations du guide interne
 * `docs/03-SEO-NUXT.md` : titre 30-70 caractères, description 70-160. Ces
 * limites sont dures — un article publié doit tenir dedans, sinon Nuxt
 * afficherait une carte SERP tronquée en preview.
 */
final class SeoMetadata
{
    private const TITLE_MIN = 30;
    private const TITLE_MAX = 70;
    private const DESCRIPTION_MIN = 70;
    private const DESCRIPTION_MAX = 160;

    private function __construct(
        private readonly string $title,
        private readonly string $description,
    ) {
    }

    public static function create(string $title, string $description): self
    {
        $trimmedTitle = trim($title);
        $trimmedDescription = trim($description);

        self::assertLength('titre SEO', $trimmedTitle, self::TITLE_MIN, self::TITLE_MAX);
        self::assertLength('description SEO', $trimmedDescription, self::DESCRIPTION_MIN, self::DESCRIPTION_MAX);

        return new self($trimmedTitle, $trimmedDescription);
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

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }
}
