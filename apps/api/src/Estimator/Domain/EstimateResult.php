<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

final class EstimateResult
{
    /** @var list<EstimateLineItem> */
    public readonly array $oneOffItems;

    /** @var list<EstimateLineItem> */
    public readonly array $recurringItems;

    /** @var list<EstimateAssumption> */
    public readonly array $assumptions;

    /**
     * @param list<EstimateLineItem>   $oneOffItems
     * @param list<EstimateLineItem>   $recurringItems
     * @param list<EstimateAssumption> $assumptions
     */
    public function __construct(
        public readonly ProjectType $recommendedProjectType,
        public readonly EstimateRange $estimateRange,
        array $oneOffItems,
        array $recurringItems,
        array $assumptions,
        public readonly PricingVersion $pricingVersion,
    ) {
        $this->oneOffItems    = $oneOffItems;
        $this->recurringItems = $recurringItems;
        $this->assumptions    = $assumptions;
    }

    public function hasRecurringServices(): bool
    {
        return $this->recurringItems !== [];
    }

    public function hasAssumptions(): bool
    {
        return $this->assumptions !== [];
    }
}
