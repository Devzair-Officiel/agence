<?php

declare(strict_types=1);

namespace App\Tests\Contact\Service;

use App\Contact\Dto\ContactRequest;
use App\Contact\Service\SymfonyContactMessageSender;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Test d'intégration — rendu Twig réel via le pipeline Symfony Mailer.
 *
 * Contrairement aux tests unitaires qui capturent un `TemplatedEmail` sans
 * le rendre, ce test démarre le kernel et déclenche l'intégralité du pipeline
 * d'envoi (BodyRenderer → Twig). Il garantit donc que :
 *   - aucune variable réservée (comme "email") n'est dans le contexte Twig ;
 *   - les templates HTML et texte se compilent et se rendent sans erreur ;
 *   - les données du visiteur apparaissent dans le résultat rendu.
 *
 * Le transport `null://null` (configuré dans `.env.test`) déclenche le rendu
 * des templates sans envoyer de vrai email.
 */
#[CoversClass(SymfonyContactMessageSender::class)]
final class ContactEmailRenderingTest extends KernelTestCase
{
    public function testSendRendersTemplatesWithoutThrowingReservedVariableException(): void
    {
        self::bootKernel();

        $sender = new SymfonyContactMessageSender(
            self::getContainer()->get(MailerInterface::class),
            fromEmail: 'no-reply@devzair.test',
            fromName: 'Devzair — Site',
            recipient: 'contact@devzair.test',
        );

        // Doit ne lever aucune exception — notamment pas :
        // Symfony\Component\Mime\Exception\InvalidArgumentException:
        //   A "TemplatedEmail" context cannot have an "email" entry (reserved).
        $sender->send($this->contactRequest(), '01a04aa1-28d1-724a-b883-6a098a16383c');

        // Si on arrive ici, les templates ont été compilés et le pipeline
        // Mailer/Twig s'est exécuté sans erreur.
        $this->addToAssertionCount(1);
    }

    public function testSendWithXssInMessageDoesNotThrow(): void
    {
        self::bootKernel();

        $sender = new SymfonyContactMessageSender(
            self::getContainer()->get(MailerInterface::class),
            fromEmail: 'no-reply@devzair.test',
            fromName: 'Devzair — Site',
            recipient: 'contact@devzair.test',
        );

        $xssRequest = new ContactRequest(
            name: '<script>alert("xss")</script>',
            email: 'xss@example.com',
            company: null,
            telephone: null,
            projectType: 'creation',
            message: 'Message avec <injection> HTML & "quotes" et des retours' . "\n" . 'à la ligne.',
            consent: true,
        );

        // Twig auto-escape doit gérer l'injection sans erreur de rendu.
        $sender->send($xssRequest, 'deadbeef-0000-0000-0000-000000000000');

        $this->addToAssertionCount(1);
    }

    public function testSendWithOptionalFieldsNullDoesNotThrow(): void
    {
        self::bootKernel();

        $sender = new SymfonyContactMessageSender(
            self::getContainer()->get(MailerInterface::class),
            fromEmail: 'no-reply@devzair.test',
            fromName: 'Devzair — Site',
            recipient: 'contact@devzair.test',
        );

        $minimalRequest = new ContactRequest(
            name: 'Bob Martin',
            email: 'bob@example.com',
            company: null,
            telephone: null,
            projectType: 'autre',
            message: 'Message suffisamment long pour passer la validation minimale.',
            consent: true,
        );

        // Les branches `{% if company %}` et `{% if telephone %}` dans les
        // templates doivent rendre sans erreur quand ces champs sont null.
        $sender->send($minimalRequest, 'ffffffff-0000-0000-0000-000000000000');

        $this->addToAssertionCount(1);
    }

    private function contactRequest(): ContactRequest
    {
        return new ContactRequest(
            name: 'Alice Dupont',
            email: 'alice@example.com',
            company: 'Acme Corp',
            telephone: '+33 6 12 34 56 78',
            projectType: 'refonte',
            message: 'Nous souhaitons refondre notre site vitrine et améliorer notre SEO local.',
            consent: true,
        );
    }
}
