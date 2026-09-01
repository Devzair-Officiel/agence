<?php

declare(strict_types=1);

namespace App\Tests\Estimator\Application\Partnership;

use App\Estimator\Application\Partnership\EstimatorPartnershipNotifier;
use App\Estimator\Domain\Partnership\EstimatorPartnershipProposal;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Uid\Uuid;

#[CoversClass(EstimatorPartnershipNotifier::class)]
final class EstimatorPartnershipNotifierTest extends TestCase
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    /** @param array<string, mixed> $estimateSnapshot */
    private function makeProposal(
        string $partnershipType = 'revenue_share',
        string $projectStage = 'idea',
        ?array $estimateSnapshot = null,
    ): EstimatorPartnershipProposal {
        $snapshot = $estimateSnapshot ?? [
            'outcome'                  => 'estimated',
            'recommended_project_type' => 'vitrinesite',
            'estimate'                 => ['minimum' => 90_000, 'maximum' => 130_000, 'currency' => 'EUR'],
            'pricing_version'          => '2026-v1',
        ];

        return EstimatorPartnershipProposal::create(
            id:                    Uuid::v7(),
            leadRequestId:         null,
            name:                  'Alice Dupont',
            email:                 'alice@example.com',
            phone:                 null,
            company:               'Startup SAS',
            projectType:           $snapshot['recommended_project_type'],
            pricingVersion:        $snapshot['pricing_version'],
            estimateMinimumMinor:  $snapshot['outcome'] === 'human_scoping_required' ? null : $snapshot['estimate']['minimum'],
            estimateMaximumMinor:  $snapshot['outcome'] === 'human_scoping_required' ? null : $snapshot['estimate']['maximum'],
            currency:              $snapshot['outcome'] === 'human_scoping_required' ? null : $snapshot['estimate']['currency'],
            partnershipType:       $partnershipType,
            projectStage:          $projectStage,
            generatesRevenue:      null,
            proposalText:          'Proposition de collaboration sérieuse.',
            relevanceText:         null,
            questionnaireSnapshot: ['project_type' => 'vitrinesite'],
            estimateSnapshot:      $snapshot,
            requestId:             'req-abc-00000001',
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

    private function notifier(MailerInterface $mailer): EstimatorPartnershipNotifier
    {
        return new EstimatorPartnershipNotifier(
            mailer: $mailer,
            estimatorPartnershipLogger: new NullLogger(),
            fromEmail: 'no-reply@devzair.test',
            fromName:  'Devzair — Site',
            recipient: 'contact@devzair.test',
            adminBaseUrl: 'http://localhost:8000',
        );
    }

    // ── Envoi réussi ──────────────────────────────────────────────────────────

    public function testNotifyPartnershipSuccessfully(): void
    {
        $captured = null;
        $proposal = $this->makeProposal();

        $this->notifier($this->capturingMailer($captured))->notify($proposal);

        self::assertInstanceOf(TemplatedEmail::class, $captured);

        // Destinataire
        self::assertSame('contact@devzair.test', $captured->getTo()[0]->getAddress());

        // Sujet contient le label humain du type de projet
        self::assertStringContainsString('Site vitrine', (string) $captured->getSubject());
        self::assertStringContainsString('[Estimateur]', (string) $captured->getSubject());

        // Contexte ne contient pas proposalText
        $ctx = $captured->getContext();
        self::assertArrayNotHasKey('proposalText', $ctx);
        self::assertArrayNotHasKey('relevanceText', $ctx);
        self::assertArrayNotHasKey('questionnaireSnapshot', $ctx);
        self::assertArrayNotHasKey('estimateSnapshot', $ctx);

        // Lien admin présent
        self::assertArrayHasKey('adminLink', $ctx);
        self::assertStringContainsString('/admin/estimator/partnerships/', $ctx['adminLink']);
        self::assertStringStartsWith('http://localhost:8000', $ctx['adminLink']);

        // Labels humains présents
        self::assertSame('Site vitrine', $ctx['projectTypeLabel']);
        self::assertSame('Partage de revenus', $ctx['partnershipTypeLabel']);
        self::assertSame('Idée', $ctx['projectStageLabel']);
    }

    // ── Outcome cadrage requis — pas de montant ───────────────────────────────

    public function testNotifyHumanScopingContainsNoBudget(): void
    {
        $captured = null;
        $humanScopingSnapshot = [
            'outcome'                  => 'human_scoping_required',
            'recommended_project_type' => 'unknown',
            'pricing_version'          => '2026-v1',
            'estimate'                 => ['minimum' => null, 'maximum' => null, 'currency' => null],
        ];
        $proposal = $this->makeProposal(estimateSnapshot: $humanScopingSnapshot);

        $this->notifier($this->capturingMailer($captured))->notify($proposal);

        self::assertInstanceOf(TemplatedEmail::class, $captured);

        $ctx = $captured->getContext();

        // L'outcome doit être mappé en label humain
        self::assertSame('Cadrage nécessaire', $ctx['outcomeLabel']);

        // Pas de montants en euros dans le contexte
        self::assertNull($ctx['minorMin']);
        self::assertNull($ctx['minorMax']);
    }

    // ── Isolation — exception SMTP silencieuse ────────────────────────────────

    public function testMailerFailureIsIsolated(): void
    {
        $transportError = new class ('SMTP timeout') extends \RuntimeException implements TransportExceptionInterface {
            public function __construct(string $message) { parent::__construct($message); }
            public function getDebug(): string { return ''; }
            public function appendDebug(string $debug): void {}
        };

        $proposal = $this->makeProposal();

        // Ne doit pas relancer l'exception
        $this->notifier($this->throwingMailer($transportError))->notify($proposal);

        // Si on arrive ici, l'exception a bien été absorbée
        $this->addToAssertionCount(1);
    }
}
