<?php

declare(strict_types=1);

namespace App\Estimator\Presentation\Http\Lead;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO HTTP de l'endpoint POST /api/estimate/lead.
 *
 * Contient les champs contact (PII) + questionnaire (sans prix).
 * L'estimateur Symfony recalcule le budget côté serveur à partir
 * du snapshot questionnaire — les prix du navigateur ne sont jamais lus.
 *
 * Honeypot : `website` — toujours laissé vide par un humain.
 */
final class EstimateLeadRequest
{
    // ── Contact ──────────────────────────────────────────────────────────────

    #[Assert\NotBlank(message: 'name est requis.')]
    #[Assert\Length(min: 2, max: 120, minMessage: 'name doit contenir au moins {{ limit }} caractères.', maxMessage: 'name ne peut pas dépasser {{ limit }} caractères.')]
    public string $name = '';

    #[Assert\NotBlank(message: 'email est requis.')]
    #[Assert\Email(message: 'email n\'est pas une adresse valide.')]
    #[Assert\Length(max: 254, maxMessage: 'email ne peut pas dépasser {{ limit }} caractères.')]
    public string $email = '';

    #[Assert\Length(max: 40, maxMessage: 'phone ne peut pas dépasser {{ limit }} caractères.')]
    #[Assert\Regex(
        pattern: '/^[0-9 +().\-]{4,40}$/u',
        message: 'phone contient des caractères non autorisés.',
        match: false,
        groups: ['phone_format'],
    )]
    public ?string $phone = null;

    #[Assert\Length(max: 160, maxMessage: 'company ne peut pas dépasser {{ limit }} caractères.')]
    public ?string $company = null;

    // ── Besoin complémentaire (libre, non pris en compte dans le calcul) ─────

    #[Assert\Length(max: 2000, maxMessage: 'additional_feature_note ne peut pas dépasser {{ limit }} caractères.')]
    public ?string $additional_feature_note = null;

    // ── Questionnaire (reprise exacte de EstimateRequest) ───────────────────

    #[Assert\NotBlank(message: 'project_type est requis.')]
    #[Assert\Choice(
        choices: ['vitrinesite', 'ecommerce', 'businessapp', 'refonte', 'other', 'unknown'],
        message: 'La valeur "{{ value }}" n\'est pas reconnue pour project_type.',
    )]
    public string $project_type = '';

    /** @var list<string> */
    #[Assert\Type('array')]
    #[Assert\Count(max: 6, maxMessage: 'objectives ne peut pas contenir plus de {{ limit }} valeurs.')]
    #[Assert\All([
        new Assert\Choice(
            choices: [
                'present_business',
                'sell_online',
                'generate_leads',
                'digitalize_process',
                'improve_existing',
                'improve_visibility',
            ],
            message: 'La valeur "{{ value }}" n\'est pas reconnue pour objectives.',
        ),
    ])]
    public array $objectives = [];

    #[Assert\NotBlank(message: 'current_situation est requis.')]
    #[Assert\Choice(
        choices: ['none', 'has_website', 'has_ecommerce', 'has_business_app', 'unknown'],
        message: 'La valeur "{{ value }}" n\'est pas reconnue pour current_situation.',
    )]
    public string $current_situation = '';

    #[Assert\NotBlank(message: 'scale est requis.')]
    #[Assert\Choice(
        choices: ['small', 'medium', 'large', 'unknown'],
        message: 'La valeur "{{ value }}" n\'est pas reconnue pour scale.',
    )]
    public string $scale = '';

    /** @var list<string> */
    #[Assert\Type('array')]
    #[Assert\Count(max: 15, maxMessage: 'features ne peut pas contenir plus de {{ limit }} valeurs.')]
    #[Assert\All([
        new Assert\Choice(
            choices: [
                'contact_form',
                'booking_form',
                'multilingual',
                'payment',
                'shipping',
                'customer_accounts',
                'authentication',
                'roles_and_permissions',
                'content_management',
                'document_generation',
                'notifications',
                'import_export',
                'api_integration',
                'stock_management',
                'promotions',
            ],
            message: 'La valeur "{{ value }}" n\'est pas reconnue pour features.',
        ),
    ])]
    public array $features = [];

    /** @var list<string> */
    #[Assert\Type('array')]
    #[Assert\Count(max: 5, maxMessage: 'content_needs ne peut pas contenir plus de {{ limit }} valeurs.')]
    #[Assert\All([
        new Assert\Choice(
            choices: [
                'visual_identity_needed',
                'visual_identity_partial',
                'content_to_write',
                'content_with_help',
                'photography_needed',
            ],
            message: 'La valeur "{{ value }}" n\'est pas reconnue pour content_needs.',
        ),
    ])]
    public array $content_needs = [];

    /** @var list<string> */
    #[Assert\Type('array')]
    #[Assert\Count(max: 4, maxMessage: 'visibility_needs ne peut pas contenir plus de {{ limit }} valeurs.')]
    #[Assert\All([
        new Assert\Choice(
            choices: [
                'seo_basic',
                'seo_advanced',
                'local_visibility',
                'editorial_strategy',
            ],
            message: 'La valeur "{{ value }}" n\'est pas reconnue pour visibility_needs.',
        ),
    ])]
    public array $visibility_needs = [];

    /** @var list<string> */
    #[Assert\Type('array')]
    #[Assert\Count(max: 5, maxMessage: 'care_needs ne peut pas contenir plus de {{ limit }} valeurs.')]
    #[Assert\All([
        new Assert\Choice(
            choices: [
                'maintenance',
                'support',
                'content_updates',
                'functional_evolution',
                'continuous_seo',
            ],
            message: 'La valeur "{{ value }}" n\'est pas reconnue pour care_needs.',
        ),
    ])]
    public array $care_needs = [];

    // ── Honeypot ─────────────────────────────────────────────────────────────

    public string $website = '';
}
