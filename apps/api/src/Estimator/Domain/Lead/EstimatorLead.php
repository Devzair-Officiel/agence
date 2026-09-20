<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Lead;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Agrégat racine : lead qualifié issu de l'estimateur.
 *
 * Invariants :
 * - `name`  non vide, ≤ 120 ;
 * - `email` non vide, ≤ 254 ;
 * - `phone` nullable, ≤ 40 si présent ;
 * - `company` nullable, ≤ 160 si présent ;
 * - `estimateMinimumMinor` et `estimateMaximumMinor` sont NULL quand
 *   outcome = "human_scoping_required", entiers positifs sinon ;
 * - Les snapshots JSONB ne peuvent jamais être vides.
 */
#[ORM\Entity]
#[ORM\Table(name: 'estimator_lead')]
class EstimatorLead
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 120)]
    private string $name;

    #[ORM\Column(name: 'email', type: Types::STRING, length: 254)]
    private string $email;

    #[ORM\Column(name: 'phone', type: Types::STRING, length: 40, nullable: true)]
    private ?string $phone;

    #[ORM\Column(name: 'company', type: Types::STRING, length: 160, nullable: true)]
    private ?string $company;

    #[ORM\Column(name: 'additional_feature_note', type: Types::TEXT, nullable: true)]
    private ?string $additionalFeatureNote;

    #[ORM\Column(name: 'project_type', type: Types::STRING, length: 80)]
    private string $projectType;

    #[ORM\Column(name: 'outcome', type: Types::STRING, length: 40)]
    private string $outcome;

    #[ORM\Column(name: 'estimate_minimum_minor', type: Types::INTEGER, nullable: true)]
    private ?int $estimateMinimumMinor;

    #[ORM\Column(name: 'estimate_maximum_minor', type: Types::INTEGER, nullable: true)]
    private ?int $estimateMaximumMinor;

    #[ORM\Column(name: 'pricing_version', type: Types::STRING, length: 40)]
    private string $pricingVersion;

    /** @var array<string, mixed> */
    #[ORM\Column(name: 'questionnaire_snapshot', type: Types::JSON)]
    private array $questionnaireSnapshot;

    /** @var array<string, mixed> */
    #[ORM\Column(name: 'estimate_snapshot', type: Types::JSON)]
    private array $estimateSnapshot;

    #[ORM\Column(name: 'request_id', type: Types::STRING, length: 36)]
    private string $requestId;

    #[ORM\Column(name: 'created_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    /**
     * @param array<string, mixed> $questionnaireSnapshot
     * @param array<string, mixed> $estimateSnapshot
     */
    private function __construct(
        Uuid $id,
        string $name,
        string $email,
        ?string $phone,
        ?string $company,
        ?string $additionalFeatureNote,
        string $projectType,
        string $outcome,
        ?int $estimateMinimumMinor,
        ?int $estimateMaximumMinor,
        string $pricingVersion,
        array $questionnaireSnapshot,
        array $estimateSnapshot,
        string $requestId,
        \DateTimeImmutable $createdAt,
    ) {
        $this->id                    = $id;
        $this->name                  = $name;
        $this->email                 = $email;
        $this->phone                 = $phone !== '' ? $phone : null;
        $this->company               = $company !== '' ? $company : null;
        $this->additionalFeatureNote = $additionalFeatureNote !== '' ? $additionalFeatureNote : null;
        $this->projectType           = $projectType;
        $this->outcome               = $outcome;
        $this->estimateMinimumMinor  = $estimateMinimumMinor;
        $this->estimateMaximumMinor  = $estimateMaximumMinor;
        $this->pricingVersion        = $pricingVersion;
        $this->questionnaireSnapshot = $questionnaireSnapshot;
        $this->estimateSnapshot      = $estimateSnapshot;
        $this->requestId             = $requestId;
        $this->createdAt             = $createdAt;
    }

    /**
     * @param array<string, mixed> $questionnaireSnapshot
     * @param array<string, mixed> $estimateSnapshot
     */
    public static function create(
        Uuid $id,
        string $name,
        string $email,
        ?string $phone,
        ?string $company,
        ?string $additionalFeatureNote,
        string $projectType,
        string $outcome,
        ?int $estimateMinimumMinor,
        ?int $estimateMaximumMinor,
        string $pricingVersion,
        array $questionnaireSnapshot,
        array $estimateSnapshot,
        string $requestId,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self(
            $id,
            $name,
            $email,
            $phone,
            $company,
            $additionalFeatureNote,
            $projectType,
            $outcome,
            $estimateMinimumMinor,
            $estimateMaximumMinor,
            $pricingVersion,
            $questionnaireSnapshot,
            $estimateSnapshot,
            $requestId,
            $createdAt,
        );
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function company(): ?string
    {
        return $this->company;
    }

    public function additionalFeatureNote(): ?string
    {
        return $this->additionalFeatureNote;
    }

    public function projectType(): string
    {
        return $this->projectType;
    }

    public function outcome(): string
    {
        return $this->outcome;
    }

    public function estimateMinimumMinor(): ?int
    {
        return $this->estimateMinimumMinor;
    }

    public function estimateMaximumMinor(): ?int
    {
        return $this->estimateMaximumMinor;
    }

    public function pricingVersion(): string
    {
        return $this->pricingVersion;
    }

    /** @return array<string, mixed> */
    public function questionnaireSnapshot(): array
    {
        return $this->questionnaireSnapshot;
    }

    /** @return array<string, mixed> */
    public function estimateSnapshot(): array
    {
        return $this->estimateSnapshot;
    }

    public function requestId(): string
    {
        return $this->requestId;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
