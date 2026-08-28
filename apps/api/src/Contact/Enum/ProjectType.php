<?php

declare(strict_types=1);

namespace App\Contact\Enum;

enum ProjectType: string
{
    case Refonte  = 'refonte';
    case Creation = 'creation';
    case Seo      = 'seo';
    case Audit    = 'audit';
    case Autre    = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::Refonte  => "Refonte d'un site existant",
            self::Creation => "Création d'un nouveau site",
            self::Seo      => 'SEO / visibilité',
            self::Audit    => 'Audit ou conseil',
            self::Autre    => 'Autre / je ne sais pas encore',
        };
    }
}
