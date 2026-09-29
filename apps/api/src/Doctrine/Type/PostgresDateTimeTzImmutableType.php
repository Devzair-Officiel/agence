<?php

declare(strict_types=1);

namespace App\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeTzImmutableType;
use Doctrine\DBAL\Types\Exception\InvalidFormat;

/**
 * Handles PostgreSQL timestamptz format: "2026-08-31 09:46:11.890284+00"
 * Doctrine expects "Y-m-d H:i:sO" but PostgreSQL omits microseconds padding
 * and uses short timezone offsets (+00 instead of +0000).
 */
final class PostgresDateTimeTzImmutableType extends DateTimeTzImmutableType
{
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?\DateTimeImmutable
    {
        if (null === $value || $value instanceof \DateTimeImmutable) {
            return $value;
        }

        $str = (string) $value;

        // Normalize "+00" or "+01" style offsets to "+0000"/"+0100" (Doctrine format O)
        $normalized = preg_replace('/([+-]\d{2})$/', '${1}00', $str);
        // Normalize "+00:00" style offsets to "+0000"
        $normalized = preg_replace('/([+-]\d{2}):(\d{2})$/', '${1}${2}', $normalized ?? $str);

        foreach (['Y-m-d H:i:s.uO', 'Y-m-d H:i:sO'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $normalized ?? $str);
            if (false !== $date) {
                return $date;
            }
        }

        // Last-resort: PHP's own parser handles most ISO 8601 variants
        try {
            return new \DateTimeImmutable($str);
        } catch (\Exception) {
        }

        throw InvalidFormat::new($value, static::class, 'Y-m-d H:i:s.uO / Y-m-d H:i:sO');
    }
}
