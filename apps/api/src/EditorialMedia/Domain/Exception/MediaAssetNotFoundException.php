<?php

declare(strict_types=1);

namespace App\EditorialMedia\Domain\Exception;

/**
 * Aucun `MediaAsset` ne correspond à l'identifiant fourni. Le contrôleur la
 * traduit systématiquement en 404 (aucune divulgation d'existence côté API).
 */
final class MediaAssetNotFoundException extends \DomainException
{
}
