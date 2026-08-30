<?php

declare(strict_types=1);

namespace App\Estimator\Presentation\Http;

use App\Estimator\Domain\EstimateAssumption;
use App\Estimator\Domain\EstimateLineItem;
use App\Estimator\Domain\EstimateResult;

/**
 * Construit le payload JSON de la réponse 200 de POST /api/estimate.
 *
 * Garanties :
 * - aucun float : tous les montants sont en minor units (centimes d'euro, int) ;
 * - outcome = « human_scoping_required » si l'assumption
 *   « unknown_project_requires_human_scoping » est présente, sinon « estimated » ;
 * - recurring_items exposent un champ « period » = « month » (V1) ;
 * - assumptions sont retournés sous forme de codes stables, jamais de labels UI.
 */
final class EstimateResponseFactory
{
    private const HUMAN_SCOPING_CODE = 'unknown_project_requires_human_scoping';
    private const RECURRING_PERIOD   = 'month';

    public function build(EstimateResult $result, string $requestId): array
    {
        return [
            'status'                   => 'ok',
            'request_id'               => $requestId,
            'outcome'                  => $this->computeOutcome($result),
            'recommended_project_type' => $result->recommendedProjectType->value,
            'estimate'                 => [
                'minimum'  => $result->estimateRange->minimum->amountMinor,
                'maximum'  => $result->estimateRange->maximum->amountMinor,
                'currency' => $result->estimateRange->currency(),
            ],
            'one_off_items'   => array_map([$this, 'mapOneOffItem'], $result->oneOffItems),
            'recurring_items' => array_map([$this, 'mapRecurringItem'], $result->recurringItems),
            'assumptions'     => array_map(
                static fn(EstimateAssumption $a): string => $a->code(),
                $result->assumptions,
            ),
            'pricing_version' => $result->pricingVersion->value(),
        ];
    }

    private function computeOutcome(EstimateResult $result): string
    {
        foreach ($result->assumptions as $assumption) {
            if ($assumption->code() === self::HUMAN_SCOPING_CODE) {
                return 'human_scoping_required';
            }
        }

        return 'estimated';
    }

    /** @return array{code: string, minimum: int, maximum: int, currency: string} */
    private function mapOneOffItem(EstimateLineItem $item): array
    {
        return [
            'code'     => $item->code,
            'minimum'  => $item->range->minimum->amountMinor,
            'maximum'  => $item->range->maximum->amountMinor,
            'currency' => $item->range->currency(),
        ];
    }

    /** @return array{code: string, minimum: int, maximum: int, currency: string, period: string} */
    private function mapRecurringItem(EstimateLineItem $item): array
    {
        return [
            'code'     => $item->code,
            'minimum'  => $item->range->minimum->amountMinor,
            'maximum'  => $item->range->maximum->amountMinor,
            'currency' => $item->range->currency(),
            'period'   => self::RECURRING_PERIOD,
        ];
    }
}
