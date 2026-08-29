<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Domain;

use App\Estimator\Domain\ContentNeed;
use App\Estimator\Domain\Exception\EstimatorInvariantViolation;
use PHPUnit\Framework\TestCase;

final class ContentNeedTest extends TestCase
{
    public function testFromStringAcceptsValidValue(): void
    {
        self::assertSame(ContentNeed::ContentToWrite, ContentNeed::fromString('content_to_write'));
    }

    public function testFromStringRejectsUnknownValue(): void
    {
        $this->expectException(EstimatorInvariantViolation::class);
        ContentNeed::fromString('video_production');
    }

    public function testFromListDeduplicates(): void
    {
        $result = ContentNeed::fromList(['content_to_write', 'content_to_write', 'photography_needed']);

        self::assertCount(2, $result);
    }

    public function testToListRoundTrips(): void
    {
        $needs = [ContentNeed::VisualIdentityNeeded, ContentNeed::PhotographyNeeded];
        $list  = ContentNeed::toList($needs);

        self::assertSame(['visual_identity_needed', 'photography_needed'], $list);
    }
}
