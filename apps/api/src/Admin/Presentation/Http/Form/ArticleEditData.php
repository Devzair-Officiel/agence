<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http\Form;

use App\Editorial\Application\Query\AdminArticleEditView;
use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\ExpertiseIdentifier;
use Symfony\Component\HttpFoundation\Request;

/**
 * DTO de l'édition. Le slug est délibérément absent des champs
 * hydratés — il n'existe qu'en lecture seule dans le template (via
 * `AdminArticleEditView`) et le contrôleur ne le repropage jamais dans une
 * commande de mutation.
 *
 * Utiliser un DTO distinct plutôt qu'un attribut `readonly=true` en HTML :
 * cela ferme la porte à toute soumission client qui tenterait d'ajouter le
 * champ à la main.
 */
final class ArticleEditData
{
    public function __construct(
        public readonly ArticleFormPayload $payload,
    ) {
    }

    public static function fromView(AdminArticleEditView $view): self
    {
        return new self(new ArticleFormPayload(
            slug: $view->slug,
            title: $view->title,
            excerpt: $view->excerpt,
            bodyMarkdown: $view->bodyMarkdown,
            seoTitle: $view->seoTitle,
            seoDescription: $view->seoDescription,
            authorName: $view->authorName,
            authorType: $view->authorType->value,
            expertises: ExpertiseIdentifier::toList($view->expertises),
        ));
    }

    public static function hydrate(Request $request, AdminArticleEditView $view): self
    {
        // Slug jamais issu de la requête : on repose sur la valeur de la vue.
        $payload = new ArticleFormPayload(
            slug: $view->slug,
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
