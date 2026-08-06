<?php

declare(strict_types=1);

namespace App\Admin\Domain;

use App\Admin\Domain\Exception\AdminUserInvariantViolation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Agrégat racine du domaine d'administration : compte administrateur.
 *
 * Modèle volontairement minimal pour la Phase 8C1 :
 * - un rôle unique implicite `ROLE_ADMIN` (pas de colonne JSON `roles`) ;
 * - pas de MFA, pas de reset par email, pas de verrouillage temporaire
 *   (le throttling Symfony natif couvre l'anti-brute-force côté firewall) ;
 * - le hash de mot de passe est calculé par le hasher `auto` (Argon2id) hors
 *   du domaine — l'entité ne stocke que la chaîne prête à persister.
 *
 * Invariants :
 * - email au format valide, longueur ≤ 180 ;
 * - `normalized_email` = `mb_strtolower(trim(email))` — clé unique en base ;
 * - `display_name` non vide, longueur ≤ 120 ;
 * - `password_hash` non vide (l'entité ne connaît jamais le mot de passe clair) ;
 * - `is_active = false` empêche toute authentification (contrôle appliqué
 *   dans `AdminUserChecker::checkPreAuth`).
 */
#[ORM\Entity]
#[ORM\Table(name: 'admin_user')]
#[ORM\UniqueConstraint(name: 'uniq_admin_user_normalized_email', columns: ['normalized_email'])]
class AdminUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    private const DISPLAY_NAME_MAX = 120;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(name: 'email', type: Types::STRING, length: 180)]
    private string $email;

    #[ORM\Column(name: 'normalized_email', type: Types::STRING, length: 180, unique: true)]
    private string $normalizedEmail;

    #[ORM\Column(name: 'password_hash', type: Types::STRING, length: 255)]
    private string $passwordHash;

    #[ORM\Column(name: 'display_name', type: Types::STRING, length: self::DISPLAY_NAME_MAX)]
    private string $displayName;

    #[ORM\Column(name: 'is_active', type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive;

    #[ORM\Column(name: 'last_login_at', type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt;

    #[ORM\Column(name: 'created_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        Uuid $id,
        AdminEmail $email,
        string $displayName,
        string $passwordHash,
        \DateTimeImmutable $now,
    ) {
        $trimmedName = trim($displayName);
        self::assertDisplayName($trimmedName);
        self::assertPasswordHash($passwordHash);

        $this->id = $id;
        $this->email = $email->display();
        $this->normalizedEmail = $email->normalized();
        $this->displayName = $trimmedName;
        $this->passwordHash = $passwordHash;
        $this->isActive = true;
        $this->lastLoginAt = null;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public static function create(
        Uuid $id,
        AdminEmail $email,
        string $displayName,
        string $passwordHash,
        \DateTimeImmutable $now,
    ): self {
        return new self($id, $email, $displayName, $passwordHash, $now);
    }

    public function changePasswordHash(string $newHash, \DateTimeImmutable $now): void
    {
        self::assertPasswordHash($newHash);
        $this->passwordHash = $newHash;
        $this->updatedAt = $now;
    }

    public function deactivate(\DateTimeImmutable $now): void
    {
        if (!$this->isActive) {
            return;
        }
        $this->isActive = false;
        $this->updatedAt = $now;
    }

    public function activate(\DateTimeImmutable $now): void
    {
        if ($this->isActive) {
            return;
        }
        $this->isActive = true;
        $this->updatedAt = $now;
    }

    public function recordSuccessfulLogin(\DateTimeImmutable $now): void
    {
        $this->lastLoginAt = $now;
        $this->updatedAt = $now;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function email(): AdminEmail
    {
        return AdminEmail::fromString($this->email);
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function lastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // --- Symfony Security ---------------------------------------------------

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return ['ROLE_ADMIN'];
    }

    public function getPassword(): string
    {
        return $this->passwordHash;
    }

    public function getUserIdentifier(): string
    {
        return $this->normalizedEmail;
    }

    public function eraseCredentials(): void
    {
        // Aucun credential en clair n'est conservé dans l'agrégat.
    }

    // --- Assertions privées ------------------------------------------------

    private static function assertDisplayName(string $displayName): void
    {
        if ($displayName === '') {
            throw new AdminUserInvariantViolation('Le nom affiché ne peut pas être vide.');
        }

        if (mb_strlen($displayName) > self::DISPLAY_NAME_MAX) {
            throw new AdminUserInvariantViolation(\sprintf(
                'Le nom affiché dépasse la longueur maximale de %d caractères.',
                self::DISPLAY_NAME_MAX,
            ));
        }
    }

    private static function assertPasswordHash(string $hash): void
    {
        if (trim($hash) === '') {
            throw new AdminUserInvariantViolation('Le hash du mot de passe ne peut pas être vide.');
        }
    }
}
