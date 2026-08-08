<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
use App\EditorialMedia\Application\Command\UploadEditorialMedia;
use App\EditorialMedia\Application\Command\UploadEditorialMediaHandler;
use App\EditorialMedia\Application\Exception\ImageDimensionsExceededException;
use App\EditorialMedia\Application\Exception\InvalidImageException;
use App\EditorialMedia\Application\Exception\MediaStorageException;
use App\EditorialMedia\Application\Exception\MediaTooLargeException;
use App\EditorialMedia\Application\Exception\UnsupportedMediaTypeException;
use App\EditorialMedia\Domain\Exception\EditorialMediaInvariantViolation;
use App\EditorialMedia\Infrastructure\Logging\MediaAdminAuditLogger;
use App\EditorialMedia\Infrastructure\Security\AdminMediaUploadRateLimiter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Formulaire d'upload d'un média (GET affiche le formulaire, POST téléverse).
 *
 * Sécurité :
 *   - `#[IsGranted('ROLE_ADMIN')]` + firewall `admin` ;
 *   - CSRF token `media_upload` ;
 *   - rate limit `admin_media_upload` (20 uploads / 10 min / admin) — consommé
 *     APRÈS validation CSRF, AVANT toute lecture du fichier ;
 *   - AUCUNE inspection binaire, décodage ou calcul de hash ici : tout le
 *     pipeline appartient au handler.
 *
 * Le POST suit un PRG-like : sur succès, redirection vers la liste avec un
 * flash message ; sur échec, ré-affichage du formulaire avec le message
 * approprié et un code HTTP significatif (415/413/422/500).
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminMediaUploadController extends AbstractController
{
    public function __construct(
        private readonly UploadEditorialMediaHandler $handler,
        private readonly AdminMediaUploadRateLimiter $rateLimiter,
        private readonly MediaAdminAuditLogger $audit,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var AdminUser $admin */
        $admin = $this->getUser();

        if ($request->isMethod('GET')) {
            return $this->renderForm(null, Response::HTTP_OK);
        }

        if (!$this->isCsrfTokenValid('media_upload', (string) $request->request->get('_csrf_token'))) {
            $this->audit->uploadFailed($admin, 'csrf_invalid');

            return $this->renderForm(
                'Le jeton de sécurité a expiré. Merci de renvoyer le formulaire.',
                Response::HTTP_FORBIDDEN,
            );
        }

        $limit = $this->rateLimiter->consume($admin);
        if (!$limit->isAccepted()) {
            $retryAfter = max(0, $limit->getRetryAfter()->getTimestamp() - time());
            $this->audit->uploadRateLimited($admin, $retryAfter);

            $response = $this->render('admin/media/rate_limited.html.twig', [
                'retry_after_seconds' => $retryAfter,
            ], new Response('', Response::HTTP_TOO_MANY_REQUESTS));
            $response->headers->set('Retry-After', (string) $retryAfter);

            return $response;
        }

        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            $this->audit->uploadFailed($admin, 'file_missing');

            return $this->renderForm('Aucun fichier reçu.', Response::HTTP_BAD_REQUEST);
        }

        if (!$file->isValid()) {
            $this->audit->uploadFailed($admin, 'upload_error');

            return $this->renderForm(
                'Le téléversement du fichier a échoué. Vérifiez sa taille et réessayez.',
                Response::HTTP_BAD_REQUEST,
            );
        }

        $sourcePath = $file->getRealPath();
        if ($sourcePath === false) {
            $this->audit->uploadFailed($admin, 'file_unreadable');

            return $this->renderForm(
                'Le fichier reçu est illisible côté serveur.',
                Response::HTTP_BAD_REQUEST,
            );
        }

        $originalName = trim($file->getClientOriginalName());
        if ($originalName === '') {
            $originalName = 'sans-nom';
        }

        try {
            $result = $this->handler->__invoke(new UploadEditorialMedia(
                sourcePath: $sourcePath,
                originalFilename: $originalName,
            ));
        } catch (UnsupportedMediaTypeException $e) {
            $this->audit->uploadFailed($admin, 'mime_unsupported');

            return $this->renderForm($e->getMessage(), Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        } catch (MediaTooLargeException $e) {
            $this->audit->uploadFailed($admin, 'file_too_large');

            return $this->renderForm($e->getMessage(), Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
        } catch (ImageDimensionsExceededException $e) {
            $this->audit->uploadFailed($admin, 'dimensions_exceeded');

            return $this->renderForm($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (InvalidImageException $e) {
            $this->audit->uploadFailed($admin, 'image_invalid');

            return $this->renderForm($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (EditorialMediaInvariantViolation $e) {
            $this->audit->uploadFailed($admin, 'invariant_violation');

            return $this->renderForm($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (MediaStorageException $e) {
            $this->audit->uploadFailed($admin, 'storage_failure');

            return $this->renderForm(
                'Le média n\'a pas pu être stocké côté serveur. Réessayez ou contactez l\'exploitation.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        $asset = $result->asset;
        $this->audit->uploaded(
            $admin,
            $asset->id()->toRfc4122(),
            $asset->mimeType()->mime(),
            $asset->sizeBytes(),
            $asset->width(),
            $asset->height(),
            $asset->sha256()->toString(),
        );

        $this->addFlash('success', \sprintf(
            'Média « %s » téléversé (%d×%d, %s).',
            $asset->originalFilename(),
            $asset->width(),
            $asset->height(),
            $asset->mimeType()->mime(),
        ));

        return new RedirectResponse(
            $this->generateUrl('admin_media_list'),
            Response::HTTP_SEE_OTHER,
        );
    }

    private function renderForm(?string $errorMessage, int $status): Response
    {
        return $this->render('admin/media/new.html.twig', [
            'error_message' => $errorMessage,
        ], new Response('', $status));
    }
}
