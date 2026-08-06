<?php

declare(strict_types=1);

namespace App\Editorial\Infrastructure\Markdown;

use App\Editorial\Application\Markdown\ArticleFrontMatter;
use App\Editorial\Application\Markdown\MarkdownParseException;
use App\Editorial\Application\Markdown\MarkdownValidationException;
use App\Editorial\Domain\ArticleSlug;
use App\Editorial\Domain\Author;
use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\ExpertiseIdentifier;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\SeoMetadata;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Parseur d'un fichier `.md` d'article.
 *
 * Format attendu :
 *
 *     ---
 *     slug: exemple-article
 *     title: "…"
 *     excerpt: "…"
 *     seo:
 *       title: "…"
 *       description: "…"
 *     author:
 *       name: "Devzair"
 *       type: organization        # organization | person
 *     expertises:
 *       - concevoir
 *       - visibilite
 *     ---
 *     ## Introduction
 *     …corps markdown…
 *
 * Règles strictes :
 * - encodage UTF-8 sans BOM ;
 * - taille ≤ 512 KB (protège contre les fichiers pathologiques) ;
 * - front matter délimité par exactement `---` sur sa propre ligne (début et fin) ;
 * - aucun champ inconnu accepté (typo → erreur) ;
 * - `publishedAt` explicitement refusé (voir ArticleFrontMatter) ;
 * - corps markdown non vide, séparé du YAML par au moins une ligne blanche.
 */
final class MarkdownArticleFileParser
{
    /**
     * Borne dure sur la taille du fichier `.md` complet (front matter + corps).
     * Alignée sur `MarkdownContentValidator::MAX_BODY_BYTES` pour garantir
     * qu'un fichier valide côté import est aussi valide côté validation
     * applicative (le corps est nécessairement plus petit que le fichier).
     */
    private const MAX_BYTES = MarkdownContentValidator::MAX_BODY_BYTES;

    private const ALLOWED_ROOT_KEYS = [
        'slug' => true,
        'title' => true,
        'excerpt' => true,
        'seo' => true,
        'author' => true,
        'expertises' => true,
    ];

    private const ALLOWED_SEO_KEYS = [
        'title' => true,
        'description' => true,
    ];

    private const ALLOWED_AUTHOR_KEYS = [
        'name' => true,
        'type' => true,
    ];

    /**
     * @return array{frontMatter: ArticleFrontMatter, body: string}
     */
    public function parseFile(string $path): array
    {
        if (!is_file($path)) {
            throw new MarkdownParseException(\sprintf('Fichier introuvable : %s', $path));
        }

        $size = filesize($path);
        if ($size === false) {
            throw new MarkdownParseException(\sprintf('Impossible de lire la taille de : %s', $path));
        }
        if ($size > self::MAX_BYTES) {
            throw new MarkdownParseException(\sprintf(
                'Fichier trop volumineux (%d octets, max %d).',
                $size,
                self::MAX_BYTES,
            ));
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new MarkdownParseException(\sprintf('Lecture du fichier impossible : %s', $path));
        }

        return $this->parseString($raw);
    }

    /**
     * @return array{frontMatter: ArticleFrontMatter, body: string}
     */
    public function parseString(string $raw): array
    {
        if (!mb_check_encoding($raw, 'UTF-8')) {
            throw new MarkdownParseException('Le fichier doit être encodé en UTF-8.');
        }
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            throw new MarkdownParseException('BOM UTF-8 détecté : à supprimer avant import.');
        }

        // Normalisation minimale des fins de ligne — YAML est sensible.
        $normalized = str_replace(["\r\n", "\r"], "\n", $raw);

        if (!str_starts_with($normalized, "---\n")) {
            throw new MarkdownParseException('Front matter YAML manquant (doit commencer par "---").');
        }

        $rest = substr($normalized, 4);
        $end = strpos($rest, "\n---");
        if ($end === false) {
            throw new MarkdownParseException('Front matter YAML non terminé (délimiteur "---" absent).');
        }

        $yamlBlock = substr($rest, 0, $end);
        $afterFence = substr($rest, $end + 4);
        // afterFence doit démarrer par une fin de ligne (---\n) puis le corps.
        if ($afterFence === '' || $afterFence[0] !== "\n") {
            throw new MarkdownParseException('Le délimiteur "---" de fin doit être seul sur sa ligne.');
        }
        $body = ltrim(substr($afterFence, 1), "\n");

        if (trim($body) === '') {
            throw new MarkdownParseException('Le corps Markdown est vide.');
        }

        try {
            /** @var mixed $parsed */
            $parsed = Yaml::parse($yamlBlock);
        } catch (ParseException $e) {
            throw new MarkdownParseException('YAML invalide : '.$e->getMessage(), previous: $e);
        }

        if (!\is_array($parsed)) {
            throw new MarkdownParseException('Front matter YAML : un objet est attendu.');
        }

        return [
            'frontMatter' => $this->hydrate($parsed),
            'body' => rtrim($body)."\n",
        ];
    }

    /**
     * @param array<int|string, mixed> $data
     */
    private function hydrate(array $data): ArticleFrontMatter
    {
        $this->assertStringKeys($data, 'racine');
        $this->assertNoUnknownKeys($data, self::ALLOWED_ROOT_KEYS, 'racine');
        $this->assertNotPresent($data, 'publishedAt', 'La date de publication est définie par la CLI, pas le front matter.');
        $this->assertNotPresent($data, 'published_at', 'La date de publication est définie par la CLI, pas le front matter.');

        $slug = $this->requireString($data, 'slug');
        $title = $this->requireString($data, 'title');
        $excerpt = $this->requireString($data, 'excerpt');

        $seoRaw = $this->requireArray($data, 'seo');
        $this->assertNoUnknownKeys($seoRaw, self::ALLOWED_SEO_KEYS, 'seo');
        $seoTitle = $this->requireString($seoRaw, 'title', 'seo.');
        $seoDescription = $this->requireString($seoRaw, 'description', 'seo.');

        $authorRaw = $this->requireArray($data, 'author');
        $this->assertNoUnknownKeys($authorRaw, self::ALLOWED_AUTHOR_KEYS, 'author');
        $authorName = $this->requireString($authorRaw, 'name', 'author.');
        $authorTypeRaw = $this->requireString($authorRaw, 'type', 'author.');
        $authorType = ArticleFrontMatter::authorTypeFromString($authorTypeRaw);

        $expertisesRaw = $this->requireArray($data, 'expertises');
        $expertises = $this->hydrateExpertises($expertisesRaw);

        try {
            $slugVo = ArticleSlug::fromString($slug);
            $seoVo = SeoMetadata::create($seoTitle, $seoDescription);
            $authorVo = $authorType === \App\Editorial\Domain\AuthorType::Person
                ? Author::person($authorName)
                : Author::organization($authorName);
        } catch (ArticleInvariantViolation $e) {
            throw new MarkdownValidationException($e->getMessage(), [$e->getMessage()]);
        }

        return ArticleFrontMatter::create(
            $slugVo,
            $title,
            $excerpt,
            $seoVo,
            $authorVo,
            $expertises,
        );
    }

    /**
     * @param array<int|string, mixed> $data
     * @param array<string, true>      $allowed
     */
    private function assertNoUnknownKeys(array $data, array $allowed, string $scope): void
    {
        foreach ($data as $key => $_) {
            if (!\is_string($key)) {
                throw new MarkdownValidationException(\sprintf(
                    'Front matter %s : clefs numériques interdites.',
                    $scope,
                ));
            }
            if (!isset($allowed[$key])) {
                throw new MarkdownValidationException(\sprintf(
                    'Champ %s inconnu : "%s". Autorisés : %s.',
                    $scope,
                    $key,
                    implode(', ', array_keys($allowed)),
                ));
            }
        }
    }

    /**
     * @param array<int|string, mixed> $data
     */
    private function assertStringKeys(array $data, string $scope): void
    {
        foreach (array_keys($data) as $key) {
            if (!\is_string($key)) {
                throw new MarkdownValidationException(\sprintf(
                    'Front matter %s : les clefs doivent être des chaînes.',
                    $scope,
                ));
            }
        }
    }

    /**
     * @param array<int|string, mixed> $data
     */
    private function assertNotPresent(array $data, string $key, string $reason): void
    {
        if (\array_key_exists($key, $data)) {
            throw new MarkdownValidationException(\sprintf(
                'Champ "%s" refusé dans le front matter. %s',
                $key,
                $reason,
            ));
        }
    }

    /**
     * @param array<int|string, mixed> $data
     */
    private function requireString(array $data, string $key, string $prefix = ''): string
    {
        $full = $prefix.$key;
        if (!\array_key_exists($key, $data)) {
            throw new MarkdownValidationException(\sprintf('Champ "%s" requis.', $full));
        }
        $value = $data[$key];
        if (!\is_string($value) || trim($value) === '') {
            throw new MarkdownValidationException(\sprintf('Champ "%s" doit être une chaîne non vide.', $full));
        }

        return $value;
    }

    /**
     * @param array<int|string, mixed> $data
     *
     * @return array<int|string, mixed>
     */
    private function requireArray(array $data, string $key): array
    {
        if (!\array_key_exists($key, $data)) {
            throw new MarkdownValidationException(\sprintf('Champ "%s" requis.', $key));
        }
        $value = $data[$key];
        if (!\is_array($value)) {
            throw new MarkdownValidationException(\sprintf('Champ "%s" doit être un objet YAML.', $key));
        }

        return $value;
    }

    /**
     * @param array<int|string, mixed> $raw
     *
     * @return list<ExpertiseIdentifier>
     */
    private function hydrateExpertises(array $raw): array
    {
        if ($raw === []) {
            throw new MarkdownValidationException('Champ "expertises" ne peut pas être vide.');
        }
        // On refuse les mappings (clefs string) — on veut une liste séquentielle.
        if (array_keys($raw) !== range(0, \count($raw) - 1)) {
            throw new MarkdownValidationException(
                'Champ "expertises" doit être une liste YAML séquentielle (tirets), pas un mapping.',
            );
        }

        $out = [];
        foreach ($raw as $index => $value) {
            if (!\is_string($value)) {
                throw new MarkdownValidationException(\sprintf(
                    'expertises[%d] doit être une chaîne.',
                    (int) $index,
                ));
            }
            $case = ExpertiseIdentifier::tryFrom($value);
            if ($case === null) {
                throw new MarkdownValidationException(\sprintf(
                    'expertises[%d] : identifiant inconnu "%s". Valeurs autorisées : %s.',
                    (int) $index,
                    $value,
                    implode(', ', array_map(static fn (ExpertiseIdentifier $e): string => $e->value, ExpertiseIdentifier::cases())),
                ));
            }
            $out[] = $case;
        }

        return $out;
    }
}
