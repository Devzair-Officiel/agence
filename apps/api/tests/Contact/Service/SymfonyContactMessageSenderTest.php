<?php

declare(strict_types=1);

namespace App\Tests\Contact\Service;

use App\Contact\Dto\ContactRequest;
use App\Contact\Exception\ContactTemporarilyUnavailableException;
use App\Contact\Service\SymfonyContactMessageSender;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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

        $sender->send($this->request(), 'req-42abcdef-1234-5678');

        self::assertInstanceOf(Email::class, $captured);
        self::assertSame('no-reply@devzair.example', $captured->getFrom()[0]->getAddress());
        self::assertSame('owner@devzair.example', $captured->getTo()[0]->getAddress());
        self::assertSame('alice@example.com', $captured->getReplyTo()[0]->getAddress());
        self::assertSame('req-42abcdef-1234-5678', $captured->getHeaders()->get('X-Request-Id')?->getBodyAsString());
        self::assertStringContainsString('Nous souhaitons refondre', (string) $captured->getTextBody());
        self::assertStringContainsString('Reçu le', (string) $captured->getTextBody());
        self::assertNull($captured->getHtmlBody(), 'Pas d\'HTML : surface d\'injection réduite.');
    }

    public function testSubjectContainsNoPiiAndOnlyShortRequestId(): void
    {
        $captured = null;

        $mailer = new class ($captured) implements MailerInterface {
            public function __construct(private ?RawMessage &$captured)
            {
            }

            public function send(RawMessage $message, ?Envelope $envelope = null): void
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

        $sender->send($this->request(), 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee');

        self::assertInstanceOf(Email::class, $captured);
        $subject = (string) $captured->getSubject();

        self::assertSame('[Devzair] Nouvelle demande de contact — aaaaaaaa', $subject);
        self::assertStringNotContainsString('Alice Dupont', $subject, 'Le nom du visiteur ne doit pas apparaître dans le sujet.');
        self::assertStringNotContainsString('alice@example.com', $subject, 'L\'email du visiteur ne doit pas apparaître dans le sujet.');
        self::assertStringNotContainsString('+33', $subject, 'Le téléphone du visiteur ne doit pas apparaître dans le sujet.');
    }

    public function testSendWithoutRecipientThrowsTemporarilyUnavailable(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::never())->method('send');

        $sender = new SymfonyContactMessageSender(
            $mailer,
            fromEmail: 'no-reply@devzair.example',
            fromName: 'Devzair — Site',
            recipient: null,
        );

        $this->expectException(ContactTemporarilyUnavailableException::class);

        $sender->send($this->request(), 'req-1');
    }

    public function testSendConvertsTransportExceptionToDomainException(): void
    {
        $transportError = new class ('SMTP timeout') extends \RuntimeException implements TransportExceptionInterface {
            public function getDebug(): string
            {
                return '';
            }

            public function appendDebug(string $debug): void
            {
            }
        };

        $mailer = new class ($transportError) implements MailerInterface {
            public function __construct(private TransportExceptionInterface $error)
            {
            }

            public function send(RawMessage $message, ?Envelope $envelope = null): void
            {
                throw $this->error;
            }
        };

        $sender = new SymfonyContactMessageSender(
            $mailer,
            fromEmail: 'no-reply@devzair.example',
            fromName: 'Devzair — Site',
            recipient: 'owner@devzair.example',
        );

        try {
            $sender->send($this->request(), 'req-transport');
            self::fail('Une ContactTemporarilyUnavailableException aurait dû être levée.');
        } catch (ContactTemporarilyUnavailableException $e) {
            self::assertSame($transportError, $e->getPrevious(), 'L\'exception de transport doit être chaînée pour le debug.');
        }
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
