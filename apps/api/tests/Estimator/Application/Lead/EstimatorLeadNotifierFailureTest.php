<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Application\Lead;

use App\Estimator\Application\Lead\EstimatorLeadNotifier;
use App\Estimator\Domain\Lead\EstimatorLead;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Uid\Uuid;

#[CoversClass(EstimatorLeadNotifier::class)]
final class EstimatorLeadNotifierFailureTest extends TestCase
{
    private function makeLead(): EstimatorLead
    {
        return EstimatorLead::create(
            id:                    Uuid::v7(),
            name:                  'Jean Dupont',
            email:                 'jean@example.com',
            phone:                 null,
            company:               null,
            additionalFeatureNote: null,
            projectType:           'vitrinesite',
            outcome:               'estimated',
            estimateMinimumMinor:  90_000,
            estimateMaximumMinor:  130_000,
            pricingVersion:        '2026-v1',
            questionnaireSnapshot: ['project_type' => 'vitrinesite'],
            estimateSnapshot:      [
                'outcome'                  => 'estimated',
                'recommended_project_type' => 'vitrinesite',
                'estimate'                 => ['minimum' => 90_000, 'maximum' => 130_000, 'currency' => 'EUR'],
                'pricing_version'          => '2026-v1',
            ],
            requestId:             'req-lead-00000001',
            createdAt:             new \DateTimeImmutable('2026-08-30T12:00:00+00:00'),
        );
    }

    private function throwingMailer(TransportExceptionInterface $error): MailerInterface
    {
        return new class ($error) implements MailerInterface {
            public function __construct(private readonly TransportExceptionInterface $error) {}

            public function send(RawMessage $message, ?Envelope $envelope = null): void
            {
                throw $this->error;
            }
        };
    }

    /**
     * Un échec SMTP ne doit jamais faire échouer la requête applicative.
     * Le lead est déjà persisté : la notification est best-effort.
     */
    public function testMailerFailureIsIsolated(): void
    {
        $transportError = new class ('SMTP connection refused') extends \RuntimeException implements TransportExceptionInterface {
            public function __construct(string $message) { parent::__construct($message); }
            public function getDebug(): string { return ''; }
            public function appendDebug(string $debug): void {}
        };

        $notifier = new EstimatorLeadNotifier(
            mailer: $this->throwingMailer($transportError),
            estimatorLeadLogger: new NullLogger(),
            fromEmail: 'no-reply@devzair.test',
            fromName:  'Devzair — Site',
            recipient: 'contact@devzair.test',
            adminBaseUrl: 'http://localhost:8000',
        );

        // Ne doit pas relancer l'exception
        $notifier->notify($this->makeLead());

        // Si on arrive ici, l'exception a bien été absorbée
        $this->addToAssertionCount(1);
    }
}
