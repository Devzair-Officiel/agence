<?php

declare(strict_types=1);

namespace App\Tests\Contact\Service;

use App\Contact\Dto\ContactRequest;
use App\Contact\Enum\ProjectType;
use App\Contact\Exception\ContactTemporarilyUnavailableException;
use App\Contact\Service\SymfonyContactMessageSender;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

#[CoversClass(SymfonyContactMessageSender::class)]
#[CoversClass(ProjectType::class)]
final class SymfonyContactMessageSenderTest extends TestCase
{
    // ── Helpers ───────────────────────────────────────────────────────────

    private function capturingMailer(?RawMessage &$captured): MailerInterface
    {
        return new class ($captured) implements MailerInterface {
            public function __construct(private ?RawMessage &$out)
            {
            }

            public function send(RawMessage $message, ?Envelope $envelope = null): void
            {
                $this->out = $message;
            }
        };
    }

    private function sender(?RawMessage &$captured, ?string $recipient = 'owner@devzair.example'): SymfonyContactMessageSender
    {
        return new SymfonyContactMessageSender(
            $this->capturingMailer($captured),
            fromEmail: 'no-reply@devzair.example',
            fromName: 'Devzair — Site',
            recipient: $recipient,
        );
    }

    private function request(
        string $name = 'Alice Dupont',
        string $email = 'alice@example.com',
        ?string $company = 'Acme',
        ?string $telephone = '+33 6 12 34 56 78',
        string $projectType = 'refonte',
        string $message = 'Nous souhaitons refondre notre site vitrine et améliorer notre SEO local.',
        bool $consent = true,
    ): ContactRequest {
        return new ContactRequest(
            name: $name,
            email: $email,
            company: $company,
            telephone: $telephone,
            projectType: $projectType,
            message: $message,
            consent: $consent,
        );
    }

    // ── Adresses & en-têtes ───────────────────────────────────────────────

    public function testFromIsControlledByAppNotVisitor(): void
    {
        $captured = null;
        $this->sender($captured)->send($this->request(), 'req-42abcdef-1234-5678');

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        self::assertSame('no-reply@devzair.example', $captured->getFrom()[0]->getAddress());
        self::assertSame('owner@devzair.example', $captured->getTo()[0]->getAddress());
    }

    public function testReplyToIsVisitorValidatedEmail(): void
    {
        $captured = null;
        $this->sender($captured)->send($this->request(), 'req-1');

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        self::assertSame('alice@example.com', $captured->getReplyTo()[0]->getAddress());
        self::assertSame('Alice Dupont', $captured->getReplyTo()[0]->getName());
    }

    public function testXRequestIdHeaderContainsFullId(): void
    {
        $captured = null;
        $id = 'aabbccdd-1111-2222-3333-444444444444';
        $this->sender($captured)->send($this->request(), $id);

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        self::assertSame($id, $captured->getHeaders()->get('X-Request-Id')?->getBodyAsString());
    }

    // ── Objet ─────────────────────────────────────────────────────────────

    public function testSubjectContainsHumanLabelNameAndUppercaseShortId(): void
    {
        $captured = null;
        $this->sender($captured)->send($this->request(projectType: 'creation'), 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee');

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        self::assertSame(
            "[Devzair] Création d'un nouveau site — Alice Dupont — #AAAAAAAA",
            (string) $captured->getSubject(),
        );
    }

    public function testSubjectShortIdIsUppercased(): void
    {
        $captured = null;
        $this->sender($captured)->send($this->request(), 'abcdef12-0000-0000-0000-000000000000');

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        self::assertStringContainsString('#ABCDEF12', (string) $captured->getSubject());
    }

    public function testSubjectContainsNoEmailOrPhone(): void
    {
        $captured = null;
        $this->sender($captured)->send(
            $this->request(email: 'alice@example.com', telephone: '+33 6 12 34 56 78'),
            'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
        );

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        $subject = (string) $captured->getSubject();

        self::assertStringNotContainsString('alice@example.com', $subject, 'Email du visiteur absent du sujet.');
        self::assertStringNotContainsString('+33', $subject, 'Téléphone du visiteur absent du sujet.');
    }

    // ── Templates ─────────────────────────────────────────────────────────

    public function testEmailUsesHtmlAndTextTemplates(): void
    {
        $captured = null;
        $this->sender($captured)->send($this->request(), 'req-1');

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        self::assertSame('contact/notification.html.twig', $captured->getHtmlTemplate());
        self::assertSame('contact/notification.txt.twig', $captured->getTextTemplate());
    }

    // ── Variable réservée "email" ──────────────────────────────────────────

    /**
     * Régression : "email" est une clé réservée de TemplatedEmail.
     * Si quelqu'un remet `'email' => ...` dans le contexte, Symfony lève
     * une `InvalidArgumentException` immédiatement dans `->context()`.
     * Ce test unitaire prouve que le mécanisme existe et que notre code ne
     * l'enfreint pas (le test suivant).
     */
    public function testTemplatedEmailThrowsImmediatelyForReservedEmailKey(): void
    {
        $this->expectException(\Symfony\Component\Mime\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/reserved/i');

        (new TemplatedEmail())->context(['email' => 'will-throw@example.com']);
    }

    public function testContextDoesNotUseReservedEmailKeyButVisitorEmail(): void
    {
        $captured = null;
        $this->sender($captured)->send($this->request(), 'req-1');

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        $ctx = $captured->getContext();

        self::assertArrayNotHasKey('email', $ctx, '"email" est réservé par TemplatedEmail — ne jamais l\'utiliser comme clé de contexte.');
        self::assertArrayHasKey('visitorEmail', $ctx);
        self::assertSame('alice@example.com', $ctx['visitorEmail']);
    }

    // ── Contexte Twig ─────────────────────────────────────────────────────

    public function testContextContainsProjectTypeEnum(): void
    {
        $captured = null;
        $this->sender($captured)->send($this->request(projectType: 'seo'), 'req-1');

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        $context = $captured->getContext();

        self::assertInstanceOf(ProjectType::class, $context['projectType']);
        self::assertSame(ProjectType::Seo, $context['projectType']);
        self::assertSame('SEO / visibilité', $context['projectType']->label());
    }

    public function testContextShortIdIsUppercased(): void
    {
        $captured = null;
        $this->sender($captured)->send($this->request(), 'abcdef12-0000-0000-0000-000000000000');

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        self::assertSame('ABCDEF12', $captured->getContext()['shortId']);
    }

    public function testContextContainsFullRequestId(): void
    {
        $captured = null;
        $id = 'aabbccdd-1111-2222-3333-444444444444';
        $this->sender($captured)->send($this->request(), $id);

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        self::assertSame($id, $captured->getContext()['requestId']);
    }

    public function testContextReceivedAtFormattedIsFrenchDate(): void
    {
        $captured = null;
        $this->sender($captured)->send($this->request(), 'req-1');

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        $formatted = $captured->getContext()['receivedAtFormatted'];

        // Format : "28 août 2026 à 23:30" — on vérifie la structure sans fixer l'heure.
        self::assertMatchesRegularExpression(
            '/^\d{1,2} [a-zéû]+ \d{4} à \d{2}:\d{2}$/',
            $formatted,
            'La date doit être au format français : "j mois YYYY à HH:MM".',
        );
    }

    public function testContextMessageIsPassedRawForTwigToEscape(): void
    {
        $xss = '<script>alert("xss")</script>';
        $captured = null;
        $this->sender($captured)->send($this->request(message: $xss . ' suffisamment long pour validation.'), 'req-1');

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        // Le message est dans le contexte tel quel : Twig auto-escape s'en charge.
        self::assertSame($xss . ' suffisamment long pour validation.', $captured->getContext()['message']);
    }

    public function testContextOptionalFieldsAreNullWhenEmpty(): void
    {
        $captured = null;
        $this->sender($captured)->send($this->request(company: null, telephone: null), 'req-1');

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        $ctx = $captured->getContext();

        self::assertNull($ctx['company']);
        self::assertNull($ctx['telephone']);
    }

    public function testContextAllRequiredKeysPresent(): void
    {
        $captured = null;
        $this->sender($captured)->send($this->request(), 'req-1');

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        $ctx = $captured->getContext();

        foreach (['name', 'email', 'company', 'telephone', 'projectType', 'message',
                  'consent', 'requestId', 'shortId', 'receivedAtFormatted'] as $key) {
            self::assertArrayHasKey($key, $ctx, "La clé « $key » est absente du contexte Twig.");
        }
    }

    // ── ProjectType enum ──────────────────────────────────────────────────

    /** @return array<string, array{string, string}> */
    public static function projectTypeLabelProvider(): array
    {
        return [
            'refonte'  => ['refonte',  "Refonte d'un site existant"],
            'creation' => ['creation', "Création d'un nouveau site"],
            'seo'      => ['seo',      'SEO / visibilité'],
            'audit'    => ['audit',    'Audit ou conseil'],
            'autre'    => ['autre',    'Autre / je ne sais pas encore'],
        ];
    }

    #[DataProvider('projectTypeLabelProvider')]
    public function testProjectTypeLabelIsHumanReadable(string $value, string $expectedLabel): void
    {
        $captured = null;
        $this->sender($captured)->send($this->request(projectType: $value), 'req-1');

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        self::assertSame($expectedLabel, $captured->getContext()['projectType']->label());
        self::assertStringContainsString($expectedLabel, (string) $captured->getSubject());
    }

    public function testUnknownProjectTypeFallsBackToAutre(): void
    {
        $captured = null;
        // On court-circuite la validation DTO pour tester la résilience du sender.
        $req = new ContactRequest(projectType: 'valeur_inconnue', name: 'Test', email: 't@t.fr',
            message: 'Message de test suffisamment long.', consent: true);
        $this->sender($captured)->send($req, 'req-1');

        self::assertInstanceOf(TemplatedEmail::class, $captured);
        self::assertSame(ProjectType::Autre, $captured->getContext()['projectType']);
    }

    // ── Résilience ────────────────────────────────────────────────────────

    public function testSendWithoutRecipientThrowsTemporarilyUnavailable(): void
    {
        $captured = null;
        $sender = $this->sender($captured, recipient: null);

        $this->expectException(ContactTemporarilyUnavailableException::class);
        $sender->send($this->request(), 'req-1');
    }

    public function testSendConvertsTransportExceptionToDomainException(): void
    {
        $transportError = new class ('SMTP timeout') extends \RuntimeException implements TransportExceptionInterface {
            public function getDebug(): string { return ''; }
            public function appendDebug(string $debug): void {}
        };

        $mailer = new class ($transportError) implements MailerInterface {
            public function __construct(private TransportExceptionInterface $error) {}
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
            self::assertSame($transportError, $e->getPrevious());
        }
    }
}
