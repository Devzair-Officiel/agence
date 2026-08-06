<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http\Form;

use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\ExpertiseIdentifier;
use Symfony\Component\HttpFoundation\Request;

/**
 * Hydratation stricte du formulaire de création. Contrairement à
 * `ArticleEditData`, le slug est ici présent — c'est le seul chemin par
 * lequel un slug peut être posé (Phase 8C : slug immuable en édition).
 */
final class ArticleCreateData
{
    public function __construct(
        public readonly ArticleFormPayload $payload,
    ) {
    }

    public static function empty(): self
    {
        return new self(new ArticleFormPayload());
    }

    public static function hydrate(Request $request): self
    {
        $payload = new ArticleFormPayload(
            slug: self::readString($request, 'slug'),
            title: self::readString($request, 'title'),
            excerpt: self::readString($request, 'excerpt'),
            bodyMarkdown: self::readString($request, 'body_markdown'),
            seoTitle: self::readString($request, 'seo_title'),
            seoDescription: self::readString($request, 'seo_description'),
            authorName: self::readString($request, 'author_name'),
            authorType: self::readString($request, 'author_type', 'organization'),
            expertises: self::readExpertises($request),
        );

        return new self($payload);
    }

    /**
     * @return list<ExpertiseIdentifier>
     */
    public function expertises(): array
    {
        return array_values(array_filter(
            array_map(
                static fn (string $value): ?ExpertiseIdentifier => ExpertiseIdentifier::tryFrom($value),
                $this->payload->expertises,
            ),
            static fn (?ExpertiseIdentifier $case): bool => $case !== null,
        ));
    }

    public function authorType(): AuthorType
    {
        return AuthorType::tryFrom($this->payload->authorType) ?? AuthorType::Organization;
    }

    private static function readString(Request $request, string $field, string $default = ''): string
    {
        $raw = $request->request->get($field, $default);

        return \is_string($raw) ? $raw : $default;
    }

    /**
     * @return list<string>
     */
    private static function readExpertises(Request $request): array
    {
        $raw = $request->request->all('expertises');
        $values = [];
        foreach ($raw as $value) {
            if (\is_string($value)) {
                $values[] = $value;
            }
        }

        return $values;
    }
}
