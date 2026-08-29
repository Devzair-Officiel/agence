<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;

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
        $currency = $estimateRange->currency();

        foreach ($oneOffItems as $item) {
            if (!$item instanceof EstimateLineItem) {
                throw EstimatorInvariantViolation::invalidResult('oneOffItems doit contenir uniquement des EstimateLineItem.');
            }
            if ($item->category !== LineItemCategory::OneOff) {
                throw EstimatorInvariantViolation::invalidResult(
                    sprintf('La ligne "%s" a la catégorie "%s" mais se trouve dans oneOffItems (attendu : one_off).', $item->code, $item->category->value)
                );
            }
            if ($item->range->currency() !== $currency) {
                throw EstimatorInvariantViolation::invalidResult(
                    sprintf('La ligne "%s" est en %s mais le résultat est en %s.', $item->code, $item->range->currency(), $currency)
                );
            }
        }

        foreach ($recurringItems as $item) {
            if (!$item instanceof EstimateLineItem) {
                throw EstimatorInvariantViolation::invalidResult('recurringItems doit contenir uniquement des EstimateLineItem.');
            }
            if ($item->category !== LineItemCategory::Recurring) {
                throw EstimatorInvariantViolation::invalidResult(
                    sprintf('La ligne "%s" a la catégorie "%s" mais se trouve dans recurringItems (attendu : recurring).', $item->code, $item->category->value)
                );
            }
            if ($item->range->currency() !== $currency) {
                throw EstimatorInvariantViolation::invalidResult(
                    sprintf('La ligne "%s" est en %s mais le résultat est en %s.', $item->code, $item->range->currency(), $currency)
                );
            }
        }

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
