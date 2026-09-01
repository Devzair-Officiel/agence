<?php

declare(strict_types=1);

namespace App\Estimator\Application\Lead;

use App\Estimator\Domain\Lead\EstimatorLead;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Envoie une notification interne à Devzair lors d'un nouveau lead estimateur.
 *
 * L'envoi est best-effort : un échec SMTP génère un avertissement en logs
 * mais ne fait pas échouer la requête (le lead est déjà persisté).
 *
 * Logs : aucun PII (pas de name, email, phone, company, additionalFeatureNote).
 * PII minimisés dans le corps du mail : pas de téléphone ni de note complémentaire.
 */
final class EstimatorLeadNotifier
{
    /** @var array<string, string> */
    private const PROJECT_TYPE_LABELS = [
        'vitrinesite'  => 'Site vitrine',
        'ecommerce'    => 'E-commerce',
        'businessapp'  => 'Application métier',
        'refonte'      => 'Refonte',
        'other'        => 'Autre projet',
        'unknown'      => 'Projet à cadrer',
    ];

    /** @var array<string, string> */
    private const OUTCOME_LABELS = [
        'estimated'               => 'Estimation disponible',
        'human_scoping_required'  => 'Cadrage nécessaire',
    ];

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $estimatorLeadLogger,
        private readonly string $fromEmail,
        private readonly string $fromName,
        private readonly string $recipient,
        private readonly string $adminBaseUrl,
    ) {}

    public function notify(EstimatorLead $lead): void
    {
        $projectTypeLabel = self::PROJECT_TYPE_LABELS[$lead->projectType()] ?? $lead->projectType();
        $outcomeLabel     = self::OUTCOME_LABELS[$lead->outcome()] ?? $lead->outcome();

        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->fromEmail, $this->fromName))
                ->to($this->recipient)
                ->replyTo($lead->email())
                ->subject(\sprintf('[Estimateur] Nouveau lead — %s', $projectTypeLabel))
                ->htmlTemplate('estimator/lead_notification.html.twig')
                ->textTemplate('estimator/lead_notification.txt.twig')
                ->context([
                    'name'             => $lead->name(),
                    'visitorEmail'     => $lead->email(),
                    'company'          => $lead->company(),
                    'projectTypeLabel' => $projectTypeLabel,
                    'outcomeLabel'     => $outcomeLabel,
                    'minorMin'         => $lead->estimateMinimumMinor(),
                    'minorMax'         => $lead->estimateMaximumMinor(),
                    'shortId'          => mb_substr($lead->requestId(), 0, 8),
                    'requestId'        => $lead->requestId(),
                    'adminLink'        => \sprintf(
                        '%s/admin/estimator/leads/%s',
                        rtrim($this->adminBaseUrl, '/'),
                        $lead->id()->toRfc4122(),
                    ),
                ]);

            $this->mailer->send($email);

            $this->estimatorLeadLogger->info('estimator_lead.notification_sent', [
                'request_id' => $lead->requestId(),
            ]);
        } catch (TransportExceptionInterface $e) {
            $this->estimatorLeadLogger->warning('estimator_lead.notification_failed', [
                'request_id' => $lead->requestId(),
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
