<?php

declare(strict_types=1);

namespace App\Tests\Admin\Presentation\Http;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Vérifie que le AdminSecurityHeadersSubscriber pose bien la CSP stricte,
 * X-Frame-Options DENY et X-Robots-Tag noindex,nofollow sur les réponses
 * /admin/*, mais reste transparent pour les autres domaines HTTP.
 */
final class AdminSecurityHeadersTest extends WebTestCase
{
    public function testLoginPageCarriesStrictSecurityHeaders(): void
    {
        $client = self::createClient();
        \App\Tests\Admin\Support\AdminDatabaseCleanup::purge(
            self::getContainer()->get(EntityManagerInterface::class),
        );

        $client->request('GET', '/admin/login');

        self::assertResponseStatusCodeSame(200);
        $headers = $client->getResponse()->headers;

        self::assertSame(
            "default-src 'none'; script-src 'none'; style-src 'self'; img-src 'self' data:; font-src 'self'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'",
            $headers->get('Content-Security-Policy'),
        );
        self::assertSame('DENY', $headers->get('X-Frame-Options'));
        self::assertSame('nosniff', $headers->get('X-Content-Type-Options'));
        self::assertSame('no-referrer', $headers->get('Referrer-Policy'));
        self::assertSame('same-origin', $headers->get('Cross-Origin-Opener-Policy'));
        self::assertSame('same-origin', $headers->get('Cross-Origin-Resource-Policy'));
        self::assertSame('noindex, nofollow', $headers->get('X-Robots-Tag'));
        self::assertStringContainsString('camera=()', (string) $headers->get('Permissions-Policy'));
    }

    public function testPublicHealthEndpointDoesNotReceiveAdminHeaders(): void
    {
        $client = self::createClient();

        $client->request('GET', '/health');

        self::assertResponseIsSuccessful();
        $headers = $client->getResponse()->headers;
        // Les headers admin ne doivent PAS déborder sur le domaine public :
        // seul /admin/* est concerné.
        self::assertNull($headers->get('Content-Security-Policy'));
        self::assertNotSame('noindex, nofollow', $headers->get('X-Robots-Tag'));
    }
}
