<?php

declare(strict_types=1);

namespace App\Tests\Contact\Controller;

use App\Contact\Controller\ContactSubmissionController;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * Vérifie que l'endpoint applique bien le rate limit et renvoie 429 avec
 * un header Retry-After. Le limiteur est vidé avant test pour ne pas
 * dépendre de l'ordre d'exécution avec les autres suites.
 */
#[CoversClass(ContactSubmissionController::class)]
final class ContactRateLimitTest extends WebTestCase
{
    private const ALLOWED_ORIGIN = 'http://localhost:3001';

    public function testExhaustingRateLimitReturns429WithRetryAfter(): void
    {
        $client = self::createClient();

        /** @var RateLimiterFactory $limiterFactory */
        $limiterFactory = $client->getContainer()->get('limiter.contact_ip');

        // On simule la consommation de toute la fenêtre par cette IP.
        // La client Symfony envoie par défaut REMOTE_ADDR=127.0.0.1.
        $burst = (int) $client->getContainer()->getParameter('app.contact.rate_limit');
        $limiter = $limiterFactory->create('127.0.0.1');
        $limiter->reset();
        // Consomme d'un coup la totalité du bucket, quelle que soit la valeur
        // effective de CONTACT_RATE_LIMIT (env test / env container).
        $limiter->consume($burst);

        $client->request(
            'POST',
            '/contact',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ORIGIN' => self::ALLOWED_ORIGIN,
            ],
            content: json_encode([
                'name' => 'Alice',
                'email' => 'alice@example.com',
                'projectType' => 'refonte',
                'message' => 'Un message de test suffisamment long pour passer.',
                'consent' => true,
                'website' => '',
            ], \JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(429);
        self::assertNotEmpty($client->getResponse()->headers->get('Retry-After'));

        $data = json_decode((string) $client->getResponse()->getContent(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertSame('rate_limited', $data['code']);

        $limiter->reset();
    }
}
