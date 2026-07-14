<?php

namespace App\Entity;

use App\Repository\BoutiqueDeliveryAccountRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Per-boutique credentials for a delivery company ("DeliveryCredential").
 *
 * Supports several auth shapes (login/password, API key, token, secret, custom
 * base URL) so it can feed any DeliveryProviderInterface connector. Sensitive
 * fields are stored pre-encrypted by EncryptionService; this entity never
 * decrypts anything itself.
 */
#[ORM\Entity(repositoryClass: BoutiqueDeliveryAccountRepository::class)]
#[ORM\Table(name: 'boutique_delivery_account')]
#[ORM\UniqueConstraint(name: 'uniq_boutique_delivery', columns: ['boutique_id', 'delivery_company_id'])]
class BoutiqueDeliveryAccount extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class, inversedBy: 'deliveryAccounts')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\ManyToOne(targetEntity: DeliveryCompany::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private DeliveryCompany $deliveryCompany,
        #[ORM\Column(length: 512)]
        private string $encryptedLogin = '',
        #[ORM\Column(length: 512)]
        private string $encryptedPassword = '',
        #[ORM\Column]
        private bool $isVerified = false,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $verifiedAt = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $lastError = null,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column(length: 512, nullable: true)]
        private ?string $encryptedApiKey = null,
        #[ORM\Column(length: 512, nullable: true)]
        private ?string $encryptedToken = null,
        #[ORM\Column(length: 512, nullable: true)]
        private ?string $encryptedSecret = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $customBaseUrl = null,
        #[ORM\Column]
        private bool $isDefault = false,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getDeliveryCompany(): DeliveryCompany
    {
        return $this->deliveryCompany;
    }

    public function getEncryptedLogin(): string
    {
        return $this->encryptedLogin;
    }

    public function getEncryptedPassword(): string
    {
        return $this->encryptedPassword;
    }

    public function setEncryptedCredentials(string $login, string $password): void
    {
        $this->encryptedLogin = $login;
        $this->encryptedPassword = $password;
        $this->touch();
    }

    public function getEncryptedApiKey(): ?string
    {
        return $this->encryptedApiKey;
    }

    public function setEncryptedApiKey(?string $encryptedApiKey): void
    {
        $this->encryptedApiKey = $encryptedApiKey;
        $this->touch();
    }

    public function getEncryptedToken(): ?string
    {
        return $this->encryptedToken;
    }

    public function setEncryptedToken(?string $encryptedToken): void
    {
        $this->encryptedToken = $encryptedToken;
        $this->touch();
    }

    public function getEncryptedSecret(): ?string
    {
        return $this->encryptedSecret;
    }

    public function setEncryptedSecret(?string $encryptedSecret): void
    {
        $this->encryptedSecret = $encryptedSecret;
        $this->touch();
    }

    public function getCustomBaseUrl(): ?string
    {
        return $this->customBaseUrl;
    }

    public function setCustomBaseUrl(?string $customBaseUrl): void
    {
        $this->customBaseUrl = $customBaseUrl;
        $this->touch();
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): void
    {
        $this->isDefault = $isDefault;
        $this->touch();
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function markAsVerified(): void
    {
        $this->isVerified = true;
        $this->verifiedAt = new \DateTimeImmutable();
        $this->lastError = null;
        $this->touch();
    }

    public function markAsUnverified(?string $error = null): void
    {
        $this->isVerified = false;
        $this->verifiedAt = null;
        $this->lastError = $error;
        $this->touch();
    }

    public function getVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->verifiedAt;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->touch();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
