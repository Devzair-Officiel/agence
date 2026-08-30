<?php

declare(strict_types=1);

namespace App\Estimator\Application\Pricing;

enum FeatureComplexity: string
{
    case Light    = 'light';
    case Medium   = 'medium';
    case Advanced = 'advanced';
}
