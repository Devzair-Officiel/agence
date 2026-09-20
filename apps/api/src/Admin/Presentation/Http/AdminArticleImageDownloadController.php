<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\Editorial\Application\Query\GetAdminArticleForEdit;
use App\Editorial\Application\Query\GetAdminArticleForEditHandler;
use App\Editorial\Domain\Exception\ArticleNotFoundException;
use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Application\Storage\MediaStorageInterface;
use App\EditorialMedia\Domain\Exception\MediaAssetNotFoundException;
use App\EditorialMedia\Domain\MediaAssetRepositoryInterface;
use App\EditorialMedia\Infrastructure\Logging\MediaAdminAuditLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * Téléchargement de l'image principale d'un article depuis l'administration.
 *
 * GET /admin/articles/{id}/image/download
 *
 * Sécurité :
 *   - `#[IsGranted('ROLE_ADMIN')]` + firewall `admin`.
 *   - GET uniquement — aucune mutation.
 *   - UUID doublement validé : regex de route + `Uuid::fromString`.
 *   - Le chemin du fichier est résolu via article → heroMediaId → storageKey,
 *     jamais depuis un paramètre client. Immunisé contre le path traversal.
 *   - 404 si l'article est inconnu, n'a pas d'image, ou si le fichier est
 *     introuvable — sans distinguer les cas (pas de fuite d'existence).
 *   - `Content-Type` issu de l'énumération `MediaType`, jamais du nom client.
 *   - `Content-Disposition: attachment` force le téléchargement (≠ preview).
 *   - Nom de fichier : `{slug-article}-image.{ext}` — propre, prévisible.
 *
 * `AdminSecurityHeadersSubscriber` applique automatiquement les en-têtes de
 * sécurité admin (`Cache-Control: private, no-store`, `X-Robots-Tag`, etc.).
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminArticleImageDownloadController extends AbstractController
{
    public function __construct(
        private readonly GetAdminArticleForEditHandler $viewHandler,
        private readonly MediaAssetRepositoryInterface $mediaRepository,
        private readonly MediaStorageInterface $storage,
        private readonly MediaAdminAuditLogger $audit,
    ) {
    }

    public function __invoke(string $id): Response
    {
        /** @var AdminUser $admin */
        $admin = $this->getUser();

        $uuid = $this->parseUuidOrNotFound($id);

        try {
            $view = $this->viewHandler->__invoke(new GetAdminArticleForEdit($uuid));
        } catch (ArticleNotFoundException) {
            throw new NotFoundHttpException();
        }

        if ($view->heroImage === null) {
            throw new NotFoundHttpException();
        }

        $mediaId = Uuid::fromString($view->heroImage->mediaId);

        try {
            $asset = $this->mediaRepository->getById($mediaId);
        } catch (MediaAssetNotFoundException) {
            throw new NotFoundHttpException();
        }

        try {
            $stream = $this->storage->openReadStream($asset->storageKey());
        } catch (MediaStorageException) {
            throw new NotFoundHttpException();
        }

        // Extension extraite de la storageKey canonique "{uuid}/original.{ext}".
        $storageKeyRaw = $asset->storageKey()->toString();
        $ext = pathinfo($storageKeyRaw, \PATHINFO_EXTENSION);
        $filename = $view->slug.'-image.'.$ext;

        $this->audit->downloaded($admin, $asset->id()->toRfc4122(), $asset->mimeType()->mime());

        $response = new StreamedResponse(static function () use ($stream): void {
            while (!feof($stream)) {
                $chunk = fread($stream, 8192);
                if ($chunk === false) {
                    break;
                }
                echo $chunk;
            }
            fclose($stream);
        });

        $response->headers->set('Content-Type', $asset->mimeType()->mime());
        $response->headers->set('Content-Length', (string) $asset->sizeBytes());
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="'.$filename.'"',
        );

        return $response;
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
