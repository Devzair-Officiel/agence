<?php

declare(strict_types=1);

namespace App\Tests\Contact\Dto;

use App\Contact\Dto\ContactRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Vérifie la matrice de contraintes déclarées sur ContactRequest.
 */
#[CoversClass(ContactRequest::class)]
final class ContactRequestValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testValidRequestPassesValidation(): void
    {
        $violations = $this->validator->validate($this->validRequest());

        self::assertCount(0, $violations, (string) $violations);
    }

    /**
     * @return array<string, array{ContactRequest, string}>
     */
    public static function invalidCases(): array
    {
        return [
            'name empty' => [
                new ContactRequest(
                    name: '',
                    email: 'a@b.com',
                    message: 'Bonjour, un message de test suffisamment long.',
                    consent: true,
                ),
                'name',
            ],
            'email invalid' => [
                new ContactRequest(
                    name: 'Alice',
                    email: 'not-an-email',
                    message: 'Bonjour, un message de test suffisamment long.',
                    consent: true,
                ),
                'email',
            ],
            'message too short' => [
                new ContactRequest(
                    name: 'Alice',
                    email: 'a@b.com',
                    message: 'court',
                    consent: true,
                ),
                'message',
            ],
            'consent false' => [
                new ContactRequest(
                    name: 'Alice',
                    email: 'a@b.com',
                    message: 'Bonjour, un message de test suffisamment long.',
                    consent: false,
                ),
                'consent',
            ],
            'project type unknown' => [
                new ContactRequest(
                    name: 'Alice',
                    email: 'a@b.com',
                    projectType: 'ghost',
                    message: 'Bonjour, un message de test suffisamment long.',
                    consent: true,
                ),
                'projectType',
            ],
            'telephone weird chars' => [
                new ContactRequest(
                    name: 'Alice',
                    email: 'a@b.com',
                    telephone: '<script>',
                    message: 'Bonjour, un message de test suffisamment long.',
                    consent: true,
                ),
                'telephone',
            ],
        ];
    }

    #[DataProvider('invalidCases')]
    public function testInvalidRequestReportsExpectedField(ContactRequest $request, string $expectedField): void
    {
        $violations = $this->validator->validate($request);
        $paths = [];

        foreach ($violations as $violation) {
            $paths[] = $violation->getPropertyPath();
        }

        self::assertContains($expectedField, $paths, sprintf(
            'Le champ "%s" devrait déclencher une violation. Reçu : %s',
            $expectedField,
            implode(', ', $paths) ?: '(aucune)',
        ));
    }

    private function validRequest(): ContactRequest
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
