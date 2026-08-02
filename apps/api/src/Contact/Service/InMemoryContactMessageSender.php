<?php

declare(strict_types=1);

namespace App\Contact\Service;

use App\Contact\Dto\ContactRequest;
use App\Contact\Exception\ContactTemporarilyUnavailableException;

/**
 * Fake pour tests : capture les envois sans appeler le Mailer.
 * Enregistré à la place de SymfonyContactMessageSender via alias en test.
 *
 * `failNextWith()` permet à un test de simuler l'indisponibilité du transport
 * SMTP sans monter un serveur : le prochain appel à `send()` lève l'exception
 * demandée, exactement comme SymfonyContactMessageSender le ferait sur un
 * échec OVHcloud (voir tests 503).
 */
final class InMemoryContactMessageSender implements ContactMessageSenderInterface
{
    /** @var list<array{request: ContactRequest, requestId: string}> */
    private array $sent = [];

    private ?\Throwable $nextFailure = null;

    public function send(ContactRequest $request, string $requestId): void
    {
        if ($this->nextFailure !== null) {
            $failure = $this->nextFailure;
            $this->nextFailure = null;

            throw $failure;
        }

        $this->sent[] = ['request' => $request, 'requestId' => $requestId];
    }

    public function failNextWith(\Throwable $exception): void
    {
        $this->nextFailure = $exception;
    }

    public function failNextTemporarily(string $message = 'Fake SMTP unavailability'): void
    {
        $this->failNextWith(new ContactTemporarilyUnavailableException($message));
    }

    /**
     * @return list<array{request: ContactRequest, requestId: string}>
     */
    public function all(): array
    {
        return $this->sent;
    }

    public function reset(): void
    {
        $this->sent = [];
        $this->nextFailure = null;
    }
}
