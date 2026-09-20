<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Partnership;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Agrégat racine : proposition de partenariat issue de l'estimateur.
 *
 * Invariants :
 * - `status` est toujours `pending_review` à la création ; seule une action
 *   humaine modifie ce statut (aucune logique d'acceptation automatique) ;
 * - `estimateMinimumMinor` / `estimateMaximumMinor` / `currency` sont NULL
 *   quand outcome = "human_scoping_required" ;
 * - `proposalText` est obligatoire (max 500 car.) ;
 * - `relevanceText` est optionnel (max 500 car.) ;
 * - Les snapshots JSONB ne peuvent jamais être vides.
 *
 * Aucun champ `percentage`, `equity`, `commissionRate`, `revenueShareRate`,
 * `valuation`, `durationMonths` — le calcul de parts est interdit en V1.
 */
#[ORM\Entity]
#[ORM\Table(name: 'estimator_partnership_proposal')]
class EstimatorPartnershipProposal
{
    public const STATUS_PENDING_REVIEW = 'pending_review';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(name: 'lead_request_id', type: Types::STRING, length: 36, nullable: true)]
    private ?string $leadRequestId;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 120)]
    private string $name;

    #[ORM\Column(name: 'email', type: Types::STRING, length: 254)]
    private string $email;

    #[ORM\Column(name: 'phone', type: Types::STRING, length: 40, nullable: true)]
    private ?string $phone;

    #[ORM\Column(name: 'company', type: Types::STRING, length: 160, nullable: true)]
    private ?string $company;

    #[ORM\Column(name: 'project_type', type: Types::STRING, length: 80)]
    private string $projectType;

    #[ORM\Column(name: 'pricing_version', type: Types::STRING, length: 40)]
    private string $pricingVersion;

    #[ORM\Column(name: 'estimate_minimum_minor', type: Types::INTEGER, nullable: true)]
    private ?int $estimateMinimumMinor;

    #[ORM\Column(name: 'estimate_maximum_minor', type: Types::INTEGER, nullable: true)]
    private ?int $estimateMaximumMinor;

    #[ORM\Column(name: 'currency', type: Types::STRING, length: 3, nullable: true)]
    private ?string $currency;

    #[ORM\Column(name: 'partnership_type', type: Types::STRING, length: 80)]
    private string $partnershipType;

    #[ORM\Column(name: 'project_stage', type: Types::STRING, length: 80)]
    private string $projectStage;

    #[ORM\Column(name: 'generates_revenue', type: Types::STRING, length: 40, nullable: true)]
    private ?string $generatesRevenue;

    #[ORM\Column(name: 'proposal_text', type: Types::TEXT)]
    private string $proposalText;

    #[ORM\Column(name: 'relevance_text', type: Types::TEXT, nullable: true)]
    private ?string $relevanceText;

    /** @var array<string, mixed> */
    #[ORM\Column(name: 'questionnaire_snapshot', type: Types::JSON)]
    private array $questionnaireSnapshot;

    /** @var array<string, mixed> */
    #[ORM\Column(name: 'estimate_snapshot', type: Types::JSON)]
    private array $estimateSnapshot;

    #[ORM\Column(name: 'request_id', type: Types::STRING, length: 36)]
    private string $requestId;

    #[ORM\Column(name: 'status', type: Types::STRING, length: 40)]
    private string $status;

    #[ORM\Column(name: 'created_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    /**
     * @param array<string, mixed> $questionnaireSnapshot
     * @param array<string, mixed> $estimateSnapshot
     */
    private function __construct(
        Uuid $id,
        ?string $leadRequestId,
        string $name,
        string $email,
        ?string $phone,
        ?string $company,
        string $projectType,
        string $pricingVersion,
        ?int $estimateMinimumMinor,
        ?int $estimateMaximumMinor,
        ?string $currency,
        string $partnershipType,
        string $projectStage,
        ?string $generatesRevenue,
        string $proposalText,
        ?string $relevanceText,
        array $questionnaireSnapshot,
        array $estimateSnapshot,
        string $requestId,
        \DateTimeImmutable $createdAt,
    ) {
        $this->id                    = $id;
        $this->leadRequestId         = ($leadRequestId !== '' ? $leadRequestId : null);
        $this->name                  = $name;
        $this->email                 = $email;
        $this->phone                 = ($phone !== '' ? $phone : null);
        $this->company               = ($company !== '' ? $company : null);
        $this->projectType           = $projectType;
        $this->pricingVersion        = $pricingVersion;
        $this->estimateMinimumMinor  = $estimateMinimumMinor;
        $this->estimateMaximumMinor  = $estimateMaximumMinor;
        $this->currency              = $currency;
        $this->partnershipType       = $partnershipType;
        $this->projectStage          = $projectStage;
        $this->generatesRevenue      = ($generatesRevenue !== '' ? $generatesRevenue : null);
        $this->proposalText          = $proposalText;
        $this->relevanceText         = ($relevanceText !== '' ? $relevanceText : null);
        $this->questionnaireSnapshot = $questionnaireSnapshot;
        $this->estimateSnapshot      = $estimateSnapshot;
        $this->requestId             = $requestId;
        $this->status                = self::STATUS_PENDING_REVIEW;
        $this->createdAt             = $createdAt;
    }

    /**
     * @param array<string, mixed> $questionnaireSnapshot
     * @param array<string, mixed> $estimateSnapshot
     */
    public static function create(
        Uuid $id,
        ?string $leadRequestId,
        string $name,
        string $email,
        ?string $phone,
        ?string $company,
        string $projectType,
        string $pricingVersion,
        ?int $estimateMinimumMinor,
        ?int $estimateMaximumMinor,
        ?string $currency,
        string $partnershipType,
        string $projectStage,
        ?string $generatesRevenue,
        string $proposalText,
        ?string $relevanceText,
        array $questionnaireSnapshot,
        array $estimateSnapshot,
        string $requestId,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self(
            $id,
            $leadRequestId,
            $name,
            $email,
            $phone,
            $company,
            $projectType,
            $pricingVersion,
            $estimateMinimumMinor,
            $estimateMaximumMinor,
            $currency,
            $partnershipType,
            $projectStage,
            $generatesRevenue,
            $proposalText,
            $relevanceText,
            $questionnaireSnapshot,
            $estimateSnapshot,
            $requestId,
            $createdAt,
        );
    }

    public function id(): Uuid { return $this->id; }
    public function leadRequestId(): ?string { return $this->leadRequestId; }
    public function name(): string { return $this->name; }
    public function email(): string { return $this->email; }
    public function phone(): ?string { return $this->phone; }
    public function company(): ?string { return $this->company; }
    public function projectType(): string { return $this->projectType; }
    public function pricingVersion(): string { return $this->pricingVersion; }
    public function estimateMinimumMinor(): ?int { return $this->estimateMinimumMinor; }
    public function estimateMaximumMinor(): ?int { return $this->estimateMaximumMinor; }
    public function currency(): ?string { return $this->currency; }
    public function partnershipType(): string { return $this->partnershipType; }
    public function projectStage(): string { return $this->projectStage; }
    public function generatesRevenue(): ?string { return $this->generatesRevenue; }
    public function proposalText(): string { return $this->proposalText; }
    public function relevanceText(): ?string { return $this->relevanceText; }

    /** @return array<string, mixed> */
    public function questionnaireSnapshot(): array { return $this->questionnaireSnapshot; }

    /** @return array<string, mixed> */
    public function estimateSnapshot(): array { return $this->estimateSnapshot; }

    public function requestId(): string { return $this->requestId; }
    public function status(): string { return $this->status; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
}
