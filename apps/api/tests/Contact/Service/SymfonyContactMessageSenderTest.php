<?php

declare(strict_types=1);

namespace App\Tests\Contact\Service;

use App\Contact\Dto\ContactRequest;
use App\Contact\Service\SymfonyContactMessageSender;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

#[CoversClass(SymfonyContactMessageSender::class)]
final class SymfonyContactMessageSenderTest extends TestCase
{
    public function testSendComposesEmailWithControlledFromAndReplyToVisitor(): void
    {
        $captured = null;

        $mailer = new class ($captured) implements MailerInterface {
            public function __construct(private ?RawMessage &$captured)
            {
            }

            public function send(RawMessage $message, ?\Symfony\Component\Mailer\Envelope $envelope = null): void
            {
                $this->captured = $message;
            }
        };

        $sender = new SymfonyContactMessageSender(
            $mailer,
            fromEmail: 'no-reply@devzair.example',
            fromName: 'Devzair — Site',
            recipient: 'owner@devzair.example',
        );

        $sender->send($this->request(), 'req-42');

        self::assertInstanceOf(Email::class, $captured);
        self::assertSame('no-reply@devzair.example', $captured->getFrom()[0]->getAddress());
        self::assertSame('owner@devzair.example', $captured->getTo()[0]->getAddress());
        self::assertSame('alice@example.com', $captured->getReplyTo()[0]->getAddress());
        self::assertSame('req-42', $captured->getHeaders()->get('X-Request-Id')?->getBodyAsString());
        self::assertStringContainsString('Nous souhaitons refondre', (string) $captured->getTextBody());
        self::assertNull($captured->getHtmlBody(), 'Pas d\'HTML : surface d\'injection réduite.');
    }

    public function testSendWithoutRecipientThrows(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::never())->method('send');

        $sender = new SymfonyContactMessageSender(
            $mailer,
            fromEmail: 'no-reply@devzair.example',
            fromName: 'Devzair — Site',
            recipient: null,
        );

        $this->expectException(\RuntimeException::class);

        $sender->send($this->request(), 'req-1');
    }

    private function request(): ContactRequest
    {
        return new ContactRequest(
            name: 'Alice Dupont',
            email: 'alice@example.com',
            company: 'Acme',
            telephone: '+33 6 12 34 56 78',
            projectType: 'refonte',
            message: 'Nous souhaitons refondre notre site vitrine et améliorer notre SEO local.',
            consent: true,
        );
    }
}
