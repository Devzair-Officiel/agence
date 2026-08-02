<?php

declare(strict_types=1);

namespace App\Contact\Service;

use App\Contact\Dto\ContactRequest;
use App\Contact\Exception\ContactTemporarilyUnavailableException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Envoie l'email de notification via Symfony Mailer.
 *
 * Sécurité :
 * - `From` piloté par l'app (jamais l'email du visiteur) pour préserver
 *   la réputation SPF/DKIM ;
 * - `Reply-To` = email du visiteur : l'équipe peut répondre directement ;
 * - le sujet ne contient ni l'email ni le téléphone du visiteur (canal de
 *   support à faible contrôle : minimisation) — cf. DEC-045 ;
 * - le corps est en texte brut ; pas d'HTML pour supprimer la surface
 *   d'injection.
 *
 * Résilience :
 * - toute exception de transport ou d'expéditeur non configuré est
 *   convertie en `ContactTemporarilyUnavailableException` que le contrôleur
 *   traduit en HTTP 503 `temporary_error`. Le visiteur reçoit une erreur
 *   honnête (« Le service est momentanément indisponible… »), le formulaire
 *   conserve ses valeurs, et aucune 202/200 mensongère n'est renvoyée.
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

        $shortRequestId = substr($requestId, 0, 8);

        $email = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($this->recipient)
            ->replyTo(new Address($request->email, $request->name))
            ->subject(sprintf('[Devzair] Nouvelle demande de contact — %s', $shortRequestId))
            ->text($this->renderBody($request, $requestId));

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

    private function renderBody(ContactRequest $request, string $requestId): string
    {
        $receivedAt = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->format('Y-m-d H:i:s \U\T\C');

        return <<<TEXT
Nouveau message reçu depuis le site Devzair.

Request-Id : {$requestId}
Reçu le    : {$receivedAt}
Nom        : {$request->name}
Email      : {$request->email}
Société    : {$this->fallback($request->company)}
Téléphone  : {$this->fallback($request->telephone)}
Type       : {$request->projectType}
Consent    : oui

Message :
{$request->message}
TEXT;
    }

    private function fallback(?string $value): string
    {
        return $value === null || $value === '' ? '(non renseigné)' : $value;
    }
}
