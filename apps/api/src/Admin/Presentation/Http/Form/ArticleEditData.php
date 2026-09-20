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
        public readonly ?\DateTimeImmutable $createdAt = null,
    ) {
    }

    public static function fromView(AdminArticleEditView $view): self
    {
        return new self(
            new ArticleFormPayload(
                slug: $view->slug,
                title: $view->title,
                excerpt: $view->excerpt,
                bodyMarkdown: $view->bodyMarkdown,
                seoTitle: $view->seoTitle,
                seoDescription: $view->seoDescription,
                authorName: $view->authorName,
                authorType: $view->authorType->value,
                expertises: ExpertiseIdentifier::toList($view->expertises),
            ),
            $view->createdAt,
        );
    }

    public static function hydrate(Request $request, AdminArticleEditView $view, FormErrorBag $errors): self
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

        return new self($payload, self::readCreatedAt($request, $view->createdAt, $errors));
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

    /**
     * Lit le champ `created_at` depuis la requête (format HTML `datetime-local` :
     * `Y-m-d\TH:i` ou `Y-m-d\TH:i:s`).
     *
     * - Absent ou vide → retourne `null` (pas de mutation de `createdAt`).
     * - Présent et valide → retourne la date parsée en UTC.
     * - Présent et invalide (format inconnu, date impossible comme 2026-02-31,
     *   warnings PHP sur dépassement) → ajoute une erreur sur le champ
     *   `created_at` et retourne `$fallback` pour que le formulaire puisse
     *   être re-rendu avec la valeur actuelle.
     *
     * Le parsing est strict : après `createFromFormat`, on vérifie
     * `getLastErrors()` pour détecter les normalisations silencieuses
     * (ex. 31 février → 3 mars).
     *
     * La timezone UTC est celle retenue par le projet (voir `SystemClock`).
     */
    private static function readCreatedAt(
        Request $request,
        \DateTimeImmutable $fallback,
        FormErrorBag $errors,
    ): ?\DateTimeImmutable {
        $raw = $request->request->get('created_at', '');
        if (!\is_string($raw) || $raw === '') {
            return null;
        }

        $tz = new \DateTimeZone('UTC');
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $raw, $tz);
        if ($parsed === false) {
            $parsed = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $raw, $tz);
        }

        if ($parsed === false) {
            $errors->addField('created_at', 'La date de création est invalide.');

            return $fallback;
        }

        $parseErrors = \DateTimeImmutable::getLastErrors();
        if ($parseErrors !== false && ($parseErrors['error_count'] > 0 || $parseErrors['warning_count'] > 0)) {
            $errors->addField('created_at', 'La date de création est invalide.');

            return $fallback;
        }

        return $parsed;
    }
}
