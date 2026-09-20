<?php

declare(strict_types=1);

namespace App\Estimator\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

/**
 * Type Doctrine pour les colonnes PostgreSQL JSONB.
 *
 * Le type intégré `json` de Doctrine génère `JSON` en DDL, mais les migrations
 * EST-6, EST-7, EST-8 ont créé les colonnes snapshot/configuration en `JSONB`
 * (stockage binaire, indexable). Ce type surcharge uniquement `getSQLDeclaration`
 * pour produire `JSONB`, tout en conservant la sérialisation/désérialisation
 * standard de `JsonType`.
 *
 * Enregistré dans `doctrine.yaml` sous la clé `jsonb` avec un `type_mappings`
 * correspondant pour que l'introspection DB reconnaisse les colonnes `jsonb`
 * existantes comme ce type.
 */
final class JsonbType extends JsonType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'JSONB';
    }
}
