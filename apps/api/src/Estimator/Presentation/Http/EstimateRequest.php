<?php

declare(strict_types=1);

namespace App\Estimator\Presentation\Http;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO HTTP public de l'endpoint POST /api/estimate.
 *
 * Reçoit les champs JSON, valide les valeurs admises et reste
 * strictement découplé du domaine Estimateur.
 * Le mapper EstimateRequestMapper convertit ce DTO en ProjectEstimateInput.
 *
 * Aucun PII n'est accepté : pas d'email, nom, téléphone, message libre.
 */
final class EstimateRequest
{
    /**
     * Type de projet sélectionné par le prospect.
     * « unknown » est une valeur légitime (parcours objectif-first).
     */
    #[Assert\NotBlank(message: 'project_type est requis.')]
    #[Assert\Choice(
        choices: ['vitrinesite', 'ecommerce', 'businessapp', 'refonte', 'other', 'unknown'],
        message: 'La valeur "{{ value }}" n\'est pas reconnue pour project_type.',
    )]
    public string $project_type = '';

    /**
     * Objectifs déclarés — utilisés par le moteur pour l'inférence
     * lorsque project_type est « unknown » ou « other ».
     *
     * @var list<string>
     */
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

    /**
     * Situation actuelle du prospect (pas de site, site existant, etc.).
     */
    #[Assert\NotBlank(message: 'current_situation est requis.')]
    #[Assert\Choice(
        choices: ['none', 'has_website', 'has_ecommerce', 'has_business_app', 'unknown'],
        message: 'La valeur "{{ value }}" n\'est pas reconnue pour current_situation.',
    )]
    public string $current_situation = '';

    /**
     * Ampleur estimée du projet.
     */
    #[Assert\NotBlank(message: 'scale est requis.')]
    #[Assert\Choice(
        choices: ['small', 'medium', 'large', 'unknown'],
        message: 'La valeur "{{ value }}" n\'est pas reconnue pour scale.',
    )]
    public string $scale = '';

    /**
     * Fonctionnalités souhaitées.
     * Maximum = nombre réel de cas de l'enum ProjectFeature.
     *
     * @var list<string>
     */
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

    /**
     * Besoins en contenu / identité visuelle.
     *
     * @var list<string>
     */
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

    /**
     * Besoins en visibilité / SEO.
     *
     * @var list<string>
     */
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

    /**
     * Besoins en accompagnement récurrent post-livraison.
     *
     * @var list<string>
     */
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
}
