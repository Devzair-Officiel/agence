<?php

declare(strict_types=1);

namespace App\EditorialMedia\Domain\Exception;

/**
 * Levée par l'agrégat `MediaAsset` ou un value object quand un invariant
 * du domaine est violé (dimension nulle, MIME hors liste, hash malformé…).
 * Ne porte aucun code HTTP — le contrôleur traduit en 4xx approprié.
 */
final class EditorialMediaInvariantViolation extends \DomainException
{
}
