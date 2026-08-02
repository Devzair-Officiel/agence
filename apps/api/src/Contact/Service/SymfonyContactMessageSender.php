<?php

declare(strict_types=1);

namespace App\Contact\Service;

use App\Contact\Dto\ContactRequest;
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
 * - le corps est en texte brut ; pas d'HTML pour supprimer la surface
 *   d'injection.
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
            throw new \RuntimeException(
                'CONTACT_RECIPIENT non configuré : impossible d\'envoyer.'
            );
        }

        $email = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($this->recipient)
            ->replyTo(new Address($request->email, $request->name))
            ->subject(sprintf('[Contact site] %s — %s', $request->name, $request->projectType))
            ->text($this->renderBody($request, $requestId));

        $email->getHeaders()->addTextHeader('X-Request-Id', $requestId);

        $this->mailer->send($email);
    }

    private function renderBody(ContactRequest $request, string $requestId): string
    {
        return <<<TEXT
Nouveau message reçu depuis le site Devzair.

Request-Id : {$requestId}
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
