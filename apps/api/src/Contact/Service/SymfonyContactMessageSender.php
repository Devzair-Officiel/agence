<?php

declare(strict_types=1);

namespace App\Contact\Service;

use App\Contact\Dto\ContactRequest;
use App\Contact\Enum\ProjectType;
use App\Contact\Exception\ContactTemporarilyUnavailableException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Envoie l'email de notification via Symfony Mailer + Twig.
 *
 * Sécurité :
 * - `From` piloté par l'app (jamais l'email du visiteur) pour préserver
 *   la réputation SPF/DKIM/DMARC ;
 * - `Reply-To` = adresse validée du visiteur : l'équipe peut répondre
 *   directement sans copier-coller l'adresse ;
 * - le sujet contient le nom et le type de projet (utiles au triage) mais
 *   jamais l'email ni le téléphone (minimisation, cf. DEC-045) ;
 * - toutes les données utilisateur transitent via le contexte Twig : elles
 *   sont échappées par auto-escape, jamais insérées en `raw`.
 *
 * Résilience :
 * - toute exception de transport est convertie en
 *   `ContactTemporarilyUnavailableException` → HTTP 503 `temporary_error`.
 */
final class SymfonyContactMessageSender implements ContactMessageSenderInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromEmail,
        private readonly string $fromName,
        private readonly ?string $recipient,
    ) {
    }

    public function send(ContactRequest $request, string $requestId): void
    {
        if ($this->recipient === null || $this->recipient === '') {
            throw new ContactTemporarilyUnavailableException(
                'CONTACT_RECIPIENT non configuré : envoi impossible.'
            );
        }

        $projectType = ProjectType::tryFrom($request->projectType) ?? ProjectType::Autre;
        $shortId = strtoupper(substr($requestId, 0, 8));
        $receivedAt = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($this->recipient)
            ->replyTo(new Address($request->email, $request->name))
            ->subject(sprintf(
                '[Devzair] %s — %s — #%s',
                $projectType->label(),
                $request->name,
                $shortId,
            ))
            ->htmlTemplate('contact/notification.html.twig')
            ->textTemplate('contact/notification.txt.twig')
            ->context([
                'name'               => $request->name,
                'visitorEmail'       => $request->email,
                'company'            => $request->company,
                'telephone'          => $request->telephone,
                'projectType'        => $projectType,
                'message'            => $request->message,
                'consent'            => $request->consent,
                'requestId'          => $requestId,
                'shortId'            => $shortId,
                'receivedAtFormatted' => $this->formatDateParis($receivedAt),
            ]);

        $email->getHeaders()->addTextHeader('X-Request-Id', $requestId);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            throw new ContactTemporarilyUnavailableException(
                'Échec du transport SMTP.',
                previous: $exception,
            );
        }
    }

    private function formatDateParis(\DateTimeImmutable $dt): string
    {
        static $months = [
            1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
            'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
        ];

        return sprintf(
            '%d %s %d à %s',
            (int) $dt->format('j'),
            $months[(int) $dt->format('n')],
            (int) $dt->format('Y'),
            $dt->format('H:i'),
        );
    }
}
