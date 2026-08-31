<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Pricing;

enum PricingConfigurationStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';
    case Archived  = 'archived';

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    public function isPublished(): bool
    {
        return $this === self::Published;
    }
}
