<?php

declare(strict_types=1);

namespace App\Estimator\Application\Lead;

use App\Estimator\Domain\Lead\EstimatorLead;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

/**
 * Envoie une notification interne à Devzair lors d'un nouveau lead estimateur.
 *
 * L'envoi est best-effort : un échec SMTP génère un avertissement en logs
 * mais ne fait pas échouer la requête (le lead est déjà persisté).
 *
 * Logs : aucun PII (pas de name, email, phone, company, additionalFeatureNote).
 */
final class EstimatorLeadNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $estimatorLeadLogger,
        private readonly string $fromEmail,
        private readonly string $fromName,
        private readonly string $recipient,
    ) {}

    public function notify(EstimatorLead $lead): void
    {
        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->fromEmail, $this->fromName))
                ->to($this->recipient)
                ->replyTo($lead->email())
                ->subject(\sprintf(
                    '[Estimateur] Nouveau lead — %s — #%s',
                    $lead->projectType(),
                    mb_substr($lead->requestId(), 0, 8),
                ))
                ->htmlTemplate('estimator/lead_notification.html.twig')
                ->textTemplate('estimator/lead_notification.txt.twig')
                ->context([
                    'name'        => $lead->name(),
                    'visitorEmail'=> $lead->email(),
                    'phone'       => $lead->phone(),
                    'company'     => $lead->company(),
                    'projectType' => $lead->projectType(),
                    'outcome'     => $lead->outcome(),
                    'minorMin'    => $lead->estimateMinimumMinor(),
                    'minorMax'    => $lead->estimateMaximumMinor(),
                    'note'        => $lead->additionalFeatureNote(),
                    'shortId'     => mb_substr($lead->requestId(), 0, 8),
                    'requestId'   => $lead->requestId(),
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
