<?php

declare(strict_types=1);

namespace App\Editorial\Application\Media;

use Symfony\Component\Uid\Uuid;

/**
 * Port applicatif : ouvre un flux public sur un média normalisé.
 *
 * Utilisé par le contrôleur public `/api/media/{id}` APRÈS que
 * `PublicMediaGateInterface::isReferencedByPublishedArticle` a validé
 * l'autorisation. Les deux ports sont volontairement séparés — un service
 * unique regrouperait la responsabilité d'autorisation et de streaming, ce
 * qui rendrait toute nouvelle politique de sécurité (rate-limit,
 * geo-blocking, hotlinking) plus difficile à insérer sans modifier
 * le contrat d'accès au binaire.
 *
 * Renvoie `null` si le média n'existe pas côté `EditorialMedia`
 * (incohérence : la porte a dit oui, mais le fichier n'existe plus). Le
 * contrôleur traduit alors en 404, JAMAIS en 500 — l'incohérence est
 * loguée en aval par l'adaptateur si nécessaire.
 */
interface PublicMediaStreamerInterface
{
    public function open(Uuid $mediaAssetId): ?PublicMediaResource;
}
