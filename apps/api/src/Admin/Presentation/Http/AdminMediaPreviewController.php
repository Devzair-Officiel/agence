<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http;

use App\Admin\Domain\AdminUser;
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
 * Prévisualisation authentifiée d'un média (Phase 9A).
 *
 * Contrat de sécurité :
 *   - `#[IsGranted('ROLE_ADMIN')]` + firewall `admin` : tout visiteur anonyme
 *     est redirigé vers `/admin/login` avant même de rentrer dans le contrôleur.
 *   - GET uniquement.
 *   - UUID doublement validé : regex de route + `Uuid::fromString`.
 *   - Le binaire est renvoyé en streaming pour ne jamais charger l'image
 *     entière en mémoire — protège les gros fichiers de la classe 8 MiB.
 *   - `AdminSecurityHeadersSubscriber` applique déjà `Cache-Control: private,
 *     no-store`, `X-Robots-Tag: noindex, nofollow`, `X-Frame-Options: DENY`,
 *     `X-Content-Type-Options: nosniff` — inutile de les re-poser ici.
 *   - `Content-Type` défini côté serveur à partir de l'énumération `MediaType`,
 *     jamais à partir du nom d'origine du client.
 *   - `Content-Disposition: inline` — l'admin visualise, il ne télécharge pas
 *     par défaut.
 */
#[IsGranted('ROLE_ADMIN')]
final class AdminMediaPreviewController extends AbstractController
{
    public function __construct(
        private readonly MediaAssetRepositoryInterface $repository,
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
            $asset = $this->repository->getById($uuid);
        } catch (MediaAssetNotFoundException) {
            throw new NotFoundHttpException();
        }

        try {
            $stream = $this->storage->openReadStream($asset->storageKey());
        } catch (MediaStorageException) {
            throw new NotFoundHttpException();
        }

        $this->audit->previewed($admin, $asset->id()->toRfc4122(), $asset->mimeType()->mime());

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
        $response->headers->set('Content-Disposition', 'inline');

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
