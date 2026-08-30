<?php

declare(strict_types=1);

namespace App\Estimator\Presentation\Http\Partnership;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO HTTP de l'endpoint POST /api/estimate/partnership.
 *
 * Contient les champs contact (PII) + questionnaire (sans prix) +
 * qualification du partenariat. L'estimation est TOUJOURS recalculée
 * côté Symfony — aucun prix frontend n'est jamais accepté.
 *
 * Honeypot : `website` — toujours laissé vide par un humain.
 */
final class EstimatorPartnershipRequest
{
    // ── Contact ──────────────────────────────────────────────────────────────

    #[Assert\NotBlank(message: 'name est requis.')]
    #[Assert\Length(min: 2, max: 120, minMessage: 'name doit contenir au moins {{ limit }} caractères.', maxMessage: 'name ne peut pas dépasser {{ limit }} caractères.')]
    public string $name = '';

    #[Assert\NotBlank(message: 'email est requis.')]
    #[Assert\Email(message: "email n'est pas une adresse valide.")]
    #[Assert\Length(max: 254, maxMessage: 'email ne peut pas dépasser {{ limit }} caractères.')]
    public string $email = '';

    #[Assert\Length(max: 40, maxMessage: 'phone ne peut pas dépasser {{ limit }} caractères.')]
    public ?string $phone = null;

    #[Assert\Length(max: 160, maxMessage: 'company ne peut pas dépasser {{ limit }} caractères.')]
    public ?string $company = null;

    // ── Corrélation lead EST-6 (optionnel) ────────────────────────────────────

    #[Assert\Length(max: 36)]
    public ?string $lead_request_id = null;

    // ── Questionnaire (reprise exacte de EstimateRequest) ───────────────────

    #[Assert\NotBlank(message: 'project_type est requis.')]
    #[Assert\Choice(
        choices: ['vitrinesite', 'ecommerce', 'businessapp', 'refonte', 'other', 'unknown'],
        message: 'La valeur "{{ value }}" n\'est pas reconnue pour project_type.',
    )]
    public string $project_type = '';

    /** @var list<string> */
    #[Assert\Type('array')]
    #[Assert\Count(max: 6)]
    #[Assert\All([
        new Assert\Choice(choices: [
            'present_business', 'sell_online', 'generate_leads',
            'digitalize_process', 'improve_existing', 'improve_visibility',
        ]),
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
    #[Assert\Count(max: 15)]
    #[Assert\All([
        new Assert\Choice(choices: [
            'contact_form', 'booking_form', 'multilingual', 'payment', 'shipping',
            'customer_accounts', 'authentication', 'roles_and_permissions',
            'content_management', 'document_generation', 'notifications',
            'import_export', 'api_integration', 'stock_management', 'promotions',
        ]),
    ])]
    public array $features = [];

    /** @var list<string> */
    #[Assert\Type('array')]
    #[Assert\Count(max: 5)]
    #[Assert\All([
        new Assert\Choice(choices: [
            'visual_identity_needed', 'visual_identity_partial',
            'content_to_write', 'content_with_help', 'photography_needed',
        ]),
    ])]
    public array $content_needs = [];

    /** @var list<string> */
    #[Assert\Type('array')]
    #[Assert\Count(max: 4)]
    #[Assert\All([
        new Assert\Choice(choices: [
            'seo_basic', 'seo_advanced', 'local_visibility', 'editorial_strategy',
        ]),
    ])]
    public array $visibility_needs = [];

    /** @var list<string> */
    #[Assert\Type('array')]
    #[Assert\Count(max: 5)]
    #[Assert\All([
        new Assert\Choice(choices: [
            'maintenance', 'support', 'content_updates',
            'functional_evolution', 'continuous_seo',
        ]),
    ])]
    public array $care_needs = [];

    // ── Qualification partenariat ─────────────────────────────────────────────

    #[Assert\NotBlank(message: 'partnership_type est requis.')]
    #[Assert\Choice(
        choices: ['revenue_share', 'equity_stake', 'commercial_collaboration', 'service_exchange', 'other'],
        message: 'La valeur "{{ value }}" n\'est pas reconnue pour partnership_type.',
    )]
    public string $partnership_type = '';

    #[Assert\NotBlank(message: 'project_stage est requis.')]
    #[Assert\Choice(
        choices: ['idea', 'in_creation', 'launched', 'with_clients', 'established'],
        message: 'La valeur "{{ value }}" n\'est pas reconnue pour project_stage.',
    )]
    public string $project_stage = '';

    #[Assert\Choice(
        choices: ['yes', 'not_yet', 'prefer_to_discuss'],
        message: 'La valeur "{{ value }}" n\'est pas reconnue pour generates_revenue.',
    )]
    public ?string $generates_revenue = null;

    #[Assert\NotBlank(message: 'proposal_text est requis.')]
    #[Assert\Length(max: 500, maxMessage: 'proposal_text ne peut pas dépasser {{ limit }} caractères.')]
    public string $proposal_text = '';

    #[Assert\Length(max: 500, maxMessage: 'relevance_text ne peut pas dépasser {{ limit }} caractères.')]
    public ?string $relevance_text = null;

    // ── Honeypot ─────────────────────────────────────────────────────────────

    public string $website = '';
}
