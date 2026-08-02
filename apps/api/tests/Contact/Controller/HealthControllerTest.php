<?php

declare(strict_types=1);

namespace App\Tests\Contact\Controller;

use App\Contact\Controller\HealthController;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(HealthController::class)]
final class HealthControllerTest extends WebTestCase
{
    public function testGetHealthReturnsOk(): void
    {
        $client = self::createClient();
        $client->request('GET', '/health');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $payload = json_decode((string) $client->getResponse()->getContent(), true, flags: \JSON_THROW_ON_ERROR);
        self::assertSame(['status' => 'ok'], $payload);
    }
}
