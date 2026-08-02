<?php

declare(strict_types=1);

namespace App\Tests\Contact\Security;

use App\Contact\Security\OriginAllowlist;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(OriginAllowlist::class)]
final class OriginAllowlistTest extends TestCase
{
    public function testAllowedOriginIsAccepted(): void
    {
        $allowlist = new OriginAllowlist(['http://localhost:3001', 'https://devzair.fr']);
        $request = Request::create('/contact', 'POST');
        $request->headers->set('Origin', 'https://devzair.fr');

        self::assertTrue($allowlist->isAllowed($request));
    }

    public function testUnknownOriginIsRejected(): void
    {
        $allowlist = new OriginAllowlist(['http://localhost:3001']);
        $request = Request::create('/contact', 'POST');
        $request->headers->set('Origin', 'https://evil.example');

        self::assertFalse($allowlist->isAllowed($request));
    }

    public function testMissingOriginIsRejected(): void
    {
        $allowlist = new OriginAllowlist(['http://localhost:3001']);
        $request = Request::create('/contact', 'POST');

        self::assertFalse($allowlist->isAllowed($request));
    }

    public function testTrailingSlashIsIgnored(): void
    {
        $allowlist = new OriginAllowlist(['https://devzair.fr']);
        $request = Request::create('/contact', 'POST');
        $request->headers->set('Origin', 'https://devzair.fr/');

        self::assertTrue($allowlist->isAllowed($request));
    }
}
