<?php

declare(strict_types=1);

namespace App\Estimator\Application\Partnership;

use App\Estimator\Domain\Partnership\EstimatorPartnershipProposal;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Envoie une notification interne à Devzair lors d'une nouvelle proposition de partenariat.
 *
 * L'envoi est best-effort : un échec SMTP génère un avertissement en logs
 * mais ne fait pas échouer la requête (la proposition est déjà persistée).
 *
 * PII minimisés : pas de proposalText, relevanceText, questionnaireSnapshot,
 * estimateSnapshot dans les logs ni dans l'email.
 */
final class EstimatorPartnershipNotifier
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
        'estimated'              => 'Estimation disponible',
        'human_scoping_required' => 'Cadrage nécessaire',
    ];

    /** @var array<string, string> */
    private const PARTNERSHIP_TYPE_LABELS = [
        'revenue_share'  => 'Partage de revenus',
        'collaboration'  => 'Collaboration',
        'subcontracting' => 'Sous-traitance',
        'referral'       => 'Apport d\'affaires',
        'other'          => 'Autre',
    ];

    /** @var array<string, string> */
    private const PROJECT_STAGE_LABELS = [
        'idea'              => 'Idée',
        'validated_concept' => 'Concept validé',
        'in_progress'       => 'En cours',
        'existing'          => 'Projet existant',
        'other'             => 'Autre',
    ];

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $estimatorPartnershipLogger,
        private readonly string $fromEmail,
        private readonly string $fromName,
        private readonly string $recipient,
        private readonly string $adminBaseUrl,
    ) {}

    public function notify(EstimatorPartnershipProposal $proposal): void
    {
        $projectTypeLabel    = self::PROJECT_TYPE_LABELS[$proposal->projectType()] ?? $proposal->projectType();
        $partnershipTypeLabel = self::PARTNERSHIP_TYPE_LABELS[$proposal->partnershipType()] ?? $proposal->partnershipType();
        $projectStageLabel   = self::PROJECT_STAGE_LABELS[$proposal->projectStage()] ?? $proposal->projectStage();
        $outcomeLabel        = self::OUTCOME_LABELS[$proposal->estimateSnapshot()['outcome'] ?? ''] ?? ($proposal->estimateSnapshot()['outcome'] ?? '');

        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->fromEmail, $this->fromName))
                ->to($this->recipient)
                ->replyTo($proposal->email())
                ->subject(\sprintf('[Estimateur] Nouvelle proposition de partenariat — %s', $projectTypeLabel))
                ->htmlTemplate('estimator/partnership_notification.html.twig')
                ->textTemplate('estimator/partnership_notification.txt.twig')
                ->context([
                    'name'                 => $proposal->name(),
                    'company'              => $proposal->company(),
                    'projectTypeLabel'     => $projectTypeLabel,
                    'partnershipTypeLabel' => $partnershipTypeLabel,
                    'projectStageLabel'    => $projectStageLabel,
                    'outcomeLabel'         => $outcomeLabel,
                    'minorMin'             => $proposal->estimateMinimumMinor(),
                    'minorMax'             => $proposal->estimateMaximumMinor(),
                    'shortId'              => mb_substr($proposal->requestId(), 0, 8),
                    'requestId'            => $proposal->requestId(),
                    'adminLink'            => \sprintf(
                        '%s/admin/estimator/partnerships/%s',
                        rtrim($this->adminBaseUrl, '/'),
                        (string) $proposal->id(),
                    ),
                ]);

            $this->mailer->send($email);

            $this->estimatorPartnershipLogger->info('estimator_partnership.notification_sent', [
                'request_id' => $proposal->requestId(),
            ]);
        } catch (TransportExceptionInterface $e) {
            $this->estimatorPartnershipLogger->warning('estimator_partnership.notification_failed', [
                'request_id' => $proposal->requestId(),
            ]);
        }
    }
}
