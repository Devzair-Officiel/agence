<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Editorial\Application\Query\GetAdminArticlePreview;
use App\Editorial\Application\Query\GetAdminArticlePreviewHandler;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Prévisualisation authentifiée d'un article (Phase 8C4).
 *
 * Contrat de sécurité :
 *   - `#[IsGranted('ROLE_ADMIN')]` + firewall `admin` → 302 vers `/admin/login`
 *     pour tout visiteur anonyme (aucune fuite du titre, du corps, ni des
 *     métadonnées ; le firewall intercepte AVANT le contrôleur) ;
 *   - route GET uniquement, aucune mutation possible (aucun CSRF nécessaire
 *     ici ; les actions Publier/Archiver/Restaurer rendues dans le template
 *     réutilisent leurs propres jetons `article_{publish,archive,restore}_{id}`
 *     déjà en place — voir `AdminArticleEditController`) ;
 *   - UUID validé strictement (`Uuid::fromString` + regex de route) : tout
 *     format invalide se traduit en 404 sans requête DB ;
 *   - `ArticleNotFoundException` du handler → 404 HTTP (pas de 500) ;
 *   - `AdminSecurityHeadersSubscriber` applique la CSP `default-src 'none'`,
 *     `X-Robots-Tag: noindex, nofollow`, `Cache-Control: private, no-store`,
 *     `X-Frame-Options: DENY` sur toutes les réponses `/admin/*` — la preview
 *     n'a besoin d'aucun header custom.
 *
 * Ce contrôleur ne renvoie JAMAIS `AdminUser`, ni session, ni jeton, ni PII
 * dans le contexte Twig : le seul objet passé est `AdminArticlePreviewView`,
 * dont chaque champ est du contenu article.
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminArticlePreviewController extends AbstractController
{
    public function __construct(
        private readonly GetAdminArticlePreviewHandler $handler,
    ) {
    }

    public function __invoke(string $id): Response
    {
        $uuid = $this->parseUuidOrNotFound($id);

        try {
            $preview = $this->handler->__invoke(new GetAdminArticlePreview($uuid));
        } catch (ArticleNotFoundException) {
            throw new NotFoundHttpException();
        }

        return $this->render('admin/articles/preview.html.twig', [
            'preview' => $preview,
        ]);
    }

    private function parseUuidOrNotFound(string $id): Uuid
    {
        try {
            return Uuid::fromString($id);
        } catch (\InvalidArgumentException) {
            throw new NotFoundHttpException();
        }
    }
}
