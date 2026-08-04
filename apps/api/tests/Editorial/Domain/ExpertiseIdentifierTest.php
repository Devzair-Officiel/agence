<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Domain;

use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use App\Editorial\Domain\ExpertiseIdentifier;
use PHPUnit\Framework\TestCase;

final class ExpertiseIdentifierTest extends TestCase
{
    public function testFromListParsesKnownValues(): void
    {
        $identifiers = ExpertiseIdentifier::fromList(['concevoir', 'visibilite']);

        self::assertSame(
            [ExpertiseIdentifier::Concevoir, ExpertiseIdentifier::Visibilite],
            $identifiers,
        );
    }

    public function testFromListDeduplicates(): void
    {
        $identifiers = ExpertiseIdentifier::fromList(['concevoir', 'concevoir', 'valoriser']);

        self::assertSame(
            [ExpertiseIdentifier::Concevoir, ExpertiseIdentifier::Valoriser],
            $identifiers,
        );
    }

    public function testFromListRejectsUnknownIdentifier(): void
    {
        $this->expectException(ArticleInvariantViolation::class);

        ExpertiseIdentifier::fromList(['unknown-pillar']);
    }

    public function testToListRoundTripsStrings(): void
    {
        $strings = ExpertiseIdentifier::toList([
            ExpertiseIdentifier::Construire,
            ExpertiseIdentifier::FaireEvoluer,
        ]);

        self::assertSame(['construire', 'faire-evoluer'], $strings);
    }
}
