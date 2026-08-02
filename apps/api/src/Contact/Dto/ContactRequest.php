<?php

declare(strict_types=1);

namespace App\Contact\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Payload attendu par POST /api/contact.
 *
 * Le champ `website` est un honeypot : rempli, il déclenche une réponse 202
 * générique sans aucun envoi d'email (voir ContactSubmissionController).
 * Le champ `turnstileToken` est vérifié côté serveur par
 * TurnstileVerifierInterface avant tout envoi.
 */
final class ContactRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le nom est requis.')]
        #[Assert\Length(min: 2, max: 120)]
        public string $name = '',

        #[Assert\NotBlank(message: 'L\'adresse email est requise.')]
        #[Assert\Email(message: 'L\'adresse email est invalide.')]
        #[Assert\Length(max: 254)]
        public string $email = '',

        #[Assert\Length(max: 160)]
        public ?string $company = null,

        #[Assert\Length(max: 40)]
        #[Assert\Regex(
            pattern: '/^[0-9 +().\-]{4,40}$/u',
            message: 'Le téléphone contient des caractères non autorisés.',
            match: true,
        )]
        public ?string $telephone = null,

        #[Assert\Choice(
            choices: ['refonte', 'creation', 'seo', 'audit', 'autre'],
            message: 'Type de projet non reconnu.',
        )]
        public string $projectType = 'autre',

        #[Assert\NotBlank(message: 'Le message est requis.')]
        #[Assert\Length(min: 20, max: 4000)]
        public string $message = '',

        #[Assert\IsTrue(message: 'Le consentement est requis.')]
        public bool $consent = false,

        // Honeypot : doit rester vide. Aucune contrainte : la présence de
        // texte est traitée dans le contrôleur (réponse 202 silencieuse).
        public string $website = '',

        // Token Turnstile côté client, vérifié côté serveur si activé.
        public ?string $turnstileToken = null,
    ) {
    }
}
