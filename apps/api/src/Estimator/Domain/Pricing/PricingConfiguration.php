<?php

declare(strict_types=1);

namespace App\Estimator\Domain\Pricing;

use App\Estimator\Domain\Pricing\Exception\InvalidPricingTransitionException;
use App\Estimator\Domain\Pricing\Exception\PricingConfigurationImmutableException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Version tarifaire persistée de l'estimateur.
 *
 * Invariants :
 * - Une version published ou archived est IMMUABLE.
 * - La publication est la SEULE transition possible depuis draft.
 * - published → archived se fait uniquement lors de la publication d'une nouvelle version.
 * - Transitions autorisées : draft → published, published → archived.
 * - Jamais : archived → published, published → draft.
 *
 * Le champ `configuration` est un snapshot complet JSONB — chaque version est
 * autonome et lisible même si une ancienne configuration était archivée.
 */
#[ORM\Entity]
#[ORM\Table(name: 'estimator_pricing_configuration')]
#[ORM\UniqueConstraint(name: 'uniq_pricing_version', columns: ['version'])]
class PricingConfiguration
{
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED  = 'archived';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 80)]
    private string $version;

    #[ORM\Column(length: 20, enumType: PricingConfigurationStatus::class)]
    private PricingConfigurationStatus $status;

    #[ORM\Column(length: 3)]
    private string $currency;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $configuration;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $createdBy;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $publishedBy;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt;

    private function __construct(
        Uuid $id,
        string $version,
        string $currency,
        array $configuration,
        ?Uuid $createdBy,
    ) {
        $this->id            = $id;
        $this->version       = $version;
        $this->status        = PricingConfigurationStatus::Draft;
        $this->currency      = $currency;
        $this->configuration = $configuration;
        $this->createdBy     = $createdBy;
        $this->publishedBy   = null;
        $this->createdAt     = new \DateTimeImmutable();
        $this->updatedAt     = new \DateTimeImmutable();
        $this->publishedAt   = null;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public static function create(
        Uuid $id,
        string $version,
        string $currency,
        array $configuration,
        ?Uuid $createdBy = null,
    ): self {
        return new self($id, $version, $currency, $configuration, $createdBy);
    }

    // ─── Mutations ───────────────────────────────────────────────────────────

    /**
     * @param array<string, mixed> $configuration
     */
    public function updateConfiguration(array $configuration): void
    {
        if (!$this->status->isEditable()) {
            throw PricingConfigurationImmutableException::cannotModify($this->version, $this->status->value);
        }

        $this->configuration = $configuration;
        $this->updatedAt     = new \DateTimeImmutable();
    }

    /**
     * Transition draft → published. Appelée par le use case PublishPricingConfiguration
     * dans une transaction qui archive simultanément l'ancienne version active.
     */
    public function publish(?Uuid $publishedBy = null): void
    {
        if ($this->status !== PricingConfigurationStatus::Draft) {
            throw InvalidPricingTransitionException::notADraft($this->version, $this->status->value);
        }

        $this->status      = PricingConfigurationStatus::Published;
        $this->publishedBy = $publishedBy;
        $this->publishedAt = new \DateTimeImmutable();
        $this->updatedAt   = new \DateTimeImmutable();
    }

    /**
     * Transition published → archived. Appelée lors de la publication d'une nouvelle version.
     */
    public function archive(): void
    {
        if ($this->status !== PricingConfigurationStatus::Published) {
            throw new \DomainException(sprintf(
                'Impossible d\'archiver la configuration "%s" : statut "%s" (attendu : published).',
                $this->version,
                $this->status->value,
            ));
        }

        $this->status    = PricingConfigurationStatus::Archived;
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ─── Accesseurs ──────────────────────────────────────────────────────────

    public function id(): Uuid
    {
        return $this->id;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function status(): PricingConfigurationStatus
    {
        return $this->status;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    /** @return array<string, mixed> */
    public function configuration(): array
    {
        return $this->configuration;
    }

    public function createdBy(): ?Uuid
    {
        return $this->createdBy;
    }

    public function publishedBy(): ?Uuid
    {
        return $this->publishedBy;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function publishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }
}
