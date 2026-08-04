<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Domain;

use App\Editorial\Domain\Author;
use App\Editorial\Domain\AuthorType;
use App\Editorial\Domain\Exception\ArticleInvariantViolation;
use PHPUnit\Framework\TestCase;

final class AuthorTest extends TestCase
{
    public function testOrganizationCarriesOrganizationType(): void
    {
        $author = Author::organization('Devzair');

        self::assertSame('Devzair', $author->name());
        self::assertSame(AuthorType::Organization, $author->type());
    }

    public function testPersonCarriesPersonType(): void
    {
        $author = Author::person('Anne Auteure');

        self::assertSame('Anne Auteure', $author->name());
        self::assertSame(AuthorType::Person, $author->type());
    }

    public function testRejectsTooShortName(): void
    {
        $this->expectException(ArticleInvariantViolation::class);

        Author::person('A');
    }

    public function testEqualityConsidersBothNameAndType(): void
    {
        $orga = Author::organization('Devzair');
        $person = Author::person('Devzair');

        self::assertFalse($orga->equals($person));
        self::assertTrue($orga->equals(Author::organization('Devzair')));
    }
}
