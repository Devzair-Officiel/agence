<?php

declare(strict_types=1);

namespace App\Contact\Service;

use App\Contact\Dto\ContactRequest;

/**
 * Contrat de dispatch d'un message de contact validé.
 *
 * Deux implémentations :
 * - SymfonyContactMessageSender : email via Symfony Mailer ;
 * - InMemoryContactMessageSender : capture en mémoire (tests).
 */
interface ContactMessageSenderInterface
{
    public function send(ContactRequest $request, string $requestId): void;
}
