<?php

declare(strict_types=1);

namespace App\Contact\Service;

use App\Contact\Dto\ContactRequest;

/**
 * Fake pour tests : capture les envois sans appeler le Mailer.
 * Enregistré à la place de SymfonyContactMessageSender via alias en test.
 */
final class InMemoryContactMessageSender implements ContactMessageSenderInterface
{
    /** @var list<array{request: ContactRequest, requestId: string}> */
    private array $sent = [];

    public function send(ContactRequest $request, string $requestId): void
    {
        $this->sent[] = ['request' => $request, 'requestId' => $requestId];
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
    }
}
