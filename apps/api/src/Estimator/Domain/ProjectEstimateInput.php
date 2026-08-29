<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

final class ProjectEstimateInput
{
    /** @var list<ProjectObjective> */
    public readonly array $objectives;

    /** @var list<ProjectFeature> */
    public readonly array $features;

    /** @var list<ContentNeed> */
    public readonly array $contentNeeds;

    /** @var list<VisibilityNeed> */
    public readonly array $visibilityNeeds;

    /** @var list<CareNeed> */
    public readonly array $careNeeds;

    /**
     * @param list<ProjectObjective> $objectives
     * @param list<ProjectFeature>   $features
     * @param list<ContentNeed>      $contentNeeds
     * @param list<VisibilityNeed>   $visibilityNeeds
     * @param list<CareNeed>         $careNeeds
     */
    public function __construct(
        public readonly ProjectType $projectType,
        array $objectives,
        public readonly CurrentSituation $currentSituation,
        public readonly ProjectScale $scale,
        array $features,
        array $contentNeeds,
        array $visibilityNeeds,
        array $careNeeds,
    ) {
        $this->objectives      = self::deduplicate($objectives);
        $this->features        = self::deduplicate($features);
        $this->contentNeeds    = self::deduplicate($contentNeeds);
        $this->visibilityNeeds = self::deduplicate($visibilityNeeds);
        $this->careNeeds       = self::deduplicate($careNeeds);
    }

    /** @param list<\BackedEnum> $items */
    private static function deduplicate(array $items): array
    {
        $result = [];
        $seen   = [];

        foreach ($items as $item) {
            $key = $item->value;
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $result[]   = $item;
            }
        }

        return $result;
    }

    public function hasFeature(ProjectFeature $feature): bool
    {
        return in_array($feature, $this->features, true);
    }

    public function hasContentNeed(ContentNeed $need): bool
    {
        return in_array($need, $this->contentNeeds, true);
    }

    public function hasVisibilityNeed(VisibilityNeed $need): bool
    {
        return in_array($need, $this->visibilityNeeds, true);
    }

    public function hasCareNeed(CareNeed $need): bool
    {
        return in_array($need, $this->careNeeds, true);
    }
}
