<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Application\Lead;

use App\Estimator\Application\Lead\EstimatorLeadNotifier;
use App\Estimator\Domain\Lead\EstimatorLead;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Uid\Uuid;

#[CoversClass(EstimatorLeadNotifier::class)]
final class EstimatorLeadNotifierTest extends TestCase
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeLead(
        string $projectType = 'vitrinesite',
        string $outcome = 'estimated',
        ?string $phone = null,
        ?string $additionalFeatureNote = null,
    ): EstimatorLead {
        return EstimatorLead::create(
            id:                    Uuid::v7(),
            name:                  'Jean Dupont',
            email:                 'jean@example.com',
            phone:                 $phone,
            company:               'ACME SARL',
            additionalFeatureNote: $additionalFeatureNote,
            projectType:           $projectType,
            outcome:               $outcome,
            estimateMinimumMinor:  $outcome === 'human_scoping_required' ? null : 90_000,
            estimateMaximumMinor:  $outcome === 'human_scoping_required' ? null : 130_000,
            pricingVersion:        '2026-v1',
            questionnaireSnapshot: ['project_type' => $projectType],
            estimateSnapshot:      [
                'outcome'                  => $outcome,
                'recommended_project_type' => $projectType,
                'estimate'                 => ['minimum' => 90_000, 'maximum' => 130_000, 'currency' => 'EUR'],
                'pricing_version'          => '2026-v1',
            ],
            requestId:             'req-lead-00000001',
            createdAt:             new \DateTimeImmutable('2026-08-30T12:00:00+00:00'),
        );
    }

    private function capturingMailer(?RawMessage &$captured): MailerInterface
    {
        return new class ($captured) implements MailerInterface {
            public function __construct(private ?RawMessage &$out) {}

            public function send(RawMessage $message, ?Envelope $envelope = null): void
            {
                $this->out = $message;
            }
        };
    }

    private function notifier(MailerInterface $mailer, string $adminBaseUrl = 'http://localhost:8000'): EstimatorLeadNotifier
    {
        return new EstimatorLeadNotifier(
            mailer:                $mailer,
            estimatorLeadLogger:   new NullLogger(),
            fromEmail:             'no-reply@devzair.test',
            fromName:              'Devzair — Site',
            recipient:             'contact@devzair.test',
            adminBaseUrl:          $adminBaseUrl,
        );
    }

    // ── Envoi réussi ──────────────────────────────────────────────────────────

    public function testNotifyLeadSuccessfully(): void
    {
        $captured = null;
        $lead     = $this->makeLead(
            phone:                 '+33 6 00 00 00 00',
            additionalFeatureNote: 'Note interne confidentielle.',
        );

        $this->notifier($this->capturingMailer($captured))->notify($lead);

        self::assertInstanceOf(TemplatedEmail::class, $captured);

        // Destinataire
        self::assertSame('contact@devzair.test', $captured->getTo()[0]->getAddress());

        // Sujet contient le label humain du type de projet
        self::assertStringContainsString('Site vitrine', (string) $captured->getSubject());
        self::assertStringContainsString('[Estimateur]', (string) $captured->getSubject());

        // PII téléphone et note exclues du contexte (minimisation)
        $ctx = $captured->getContext();
        self::assertArrayNotHasKey('phone', $ctx);
        self::assertArrayNotHasKey('additionalFeatureNote', $ctx);

        // Lien admin présent et pointe vers l'entité (id), pas vers requestId
        self::assertArrayHasKey('adminLink', $ctx);
        self::assertStringContainsString('/admin/estimator/leads/', $ctx['adminLink']);
        self::assertStringContainsString($lead->id()->toRfc4122(), $ctx['adminLink']);
        self::assertStringNotContainsString($lead->requestId(), $ctx['adminLink']);
        self::assertStringStartsWith('http://localhost:8000', $ctx['adminLink']);

        // Labels humains présents
        self::assertSame('Site vitrine', $ctx['projectTypeLabel']);
        self::assertSame('Estimation disponible', $ctx['outcomeLabel']);
    }

    // ── URL de production — aucun lien localhost ──────────────────────────────

    public function testAdminLinkUsesProductionUrlNotLocalhost(): void
    {
        $captured = null;
        $lead     = $this->makeLead();

        $this->notifier($this->capturingMailer($captured), 'https://devzair.example')->notify($lead);

        self::assertInstanceOf(TemplatedEmail::class, $captured);

        $ctx = $captured->getContext();
        self::assertArrayHasKey('adminLink', $ctx);
        self::assertStringStartsWith('https://devzair.example', $ctx['adminLink']);
        self::assertStringNotContainsString('localhost', $ctx['adminLink']);
        self::assertStringContainsString($lead->id()->toRfc4122(), $ctx['adminLink']);
    }

    // ── Outcome cadrage requis — pas de montant ───────────────────────────────

    public function testNotifyHumanScopingContainsNoBudget(): void
    {
        $captured = null;
        $lead     = $this->makeLead(projectType: 'unknown', outcome: 'human_scoping_required');

        $this->notifier($this->capturingMailer($captured))->notify($lead);

        self::assertInstanceOf(TemplatedEmail::class, $captured);

        $ctx = $captured->getContext();
        self::assertSame('Cadrage nécessaire', $ctx['outcomeLabel']);
        self::assertNull($ctx['minorMin']);
        self::assertNull($ctx['minorMax']);
    }
}
