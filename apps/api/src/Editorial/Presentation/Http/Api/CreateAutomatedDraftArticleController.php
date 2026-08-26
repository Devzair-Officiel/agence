<?php

declare(strict_types=1);

namespace App\Editorial\Presentation\Http\Api;

use App\Editorial\Application\Command\CreateDraftArticle;
use App\Editorial\Application\Command\CreateDraftArticleHandler;
use App\Editorial\Application\Markdown\MarkdownValidationException;
use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\Exception\ArticleSlugAlreadyExistsException;
use App\Editorial\Domain\ExpertiseIdentifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateAutomatedDraftArticleController extends AbstractController
{
    public function __construct(
        private readonly CreateDraftArticleHandler $handler,

        #[Autowire('%env(N8N_EDITORIAL_TOKEN)%')]
        private readonly string $n8nEditorialToken,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        /*
         * Authentification machine-to-machine.
         */
        $authorization = $request->headers->get('Authorization');

        if ($authorization === null || !str_starts_with($authorization, 'Bearer ')) {
            return $this->json([
                'error' => 'unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = substr($authorization, 7);

        if (
            $token === ''
            || !hash_equals($this->n8nEditorialToken, $token)
        ) {
            return $this->json([
                'error' => 'unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        /*
         * Lecture stricte du JSON.
         */
        try {
            $payload = $request->toArray();
        } catch (\Throwable) {
            return $this->json([
                'error' => 'invalid_json',
            ], Response::HTTP_BAD_REQUEST);
        }

        /*
         * Champs texte obligatoires.
         */
        $requiredStringFields = [
            'slug',
            'title',
            'excerpt',
            'body_markdown',
            'seo_title',
            'seo_description',
            'author_name',
        ];

        foreach ($requiredStringFields as $field) {
            if (
                !array_key_exists($field, $payload)
                || !\is_string($payload[$field])
            ) {
                return $this->json([
                    'error' => 'invalid_payload',
                    'field' => $field,
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        /*
         * Author type.
         *
         * Même comportement que ton formulaire admin :
         * fallback sur Organization si la valeur est absente/inconnue.
         */
        $authorTypeRaw = $payload['author_type'] ?? 'organization';

        $authorType = \is_string($authorTypeRaw)
            ? AuthorType::tryFrom($authorTypeRaw) ?? AuthorType::Organization
            : AuthorType::Organization;

        /*
         * Expertises.
         */
        $expertiseValues = $payload['expertises'] ?? [];

        if (!\is_array($expertiseValues)) {
            return $this->json([
                'error' => 'invalid_payload',
                'field' => 'expertises',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        foreach ($expertiseValues as $value) {
            if (!\is_string($value)) {
                return $this->json([
                    'error' => 'invalid_payload',
                    'field' => 'expertises',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        try {
            $expertises = ExpertiseIdentifier::fromList(
                array_values($expertiseValues),
            );

            $result = ($this->handler)(new CreateDraftArticle(
                slug: $payload['slug'],
                title: $payload['title'],
                excerpt: $payload['excerpt'],
                bodyMarkdown: $payload['body_markdown'],
                seoTitle: $payload['seo_title'],
                seoDescription: $payload['seo_description'],
                authorName: $payload['author_name'],
                authorType: $authorType,
                expertises: $expertises,
            ));
        } catch (ArticleSlugAlreadyExistsException $e) {
            return $this->json([
                'error' => 'slug_conflict',
                'message' => $e->getMessage(),
            ], Response::HTTP_CONFLICT);
        } catch (MarkdownValidationException $e) {
            return $this->json([
                'error' => 'markdown_invalid',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (ArticleInvariantViolation $e) {
            return $this->json([
                'error' => 'invariant_violation',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json([
            'id' => $result->article->id()->toRfc4122(),
            'slug' => $result->article->slug()->value(),
            'status' => 'draft',
        ], Response::HTTP_CREATED);
    }
}
