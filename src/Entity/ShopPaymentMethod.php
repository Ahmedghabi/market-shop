<?php

namespace App\Entity;

use App\Repository\ShopPaymentMethodRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopPaymentMethodRepository::class)]
#[ORM\Table(name: 'shop_payment_method')]
#[ORM\UniqueConstraint(name: 'uniq_shop_payment_method', columns: ['boutique_id', 'payment_method_id'])]
class ShopPaymentMethod extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\ManyToOne(targetEntity: PaymentMethod::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private PaymentMethod $paymentMethod,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private int $displayOrder = 0,
        #[ORM\Column(nullable: true)]
        private ?int $minimumAmountCents = null,
        #[ORM\Column(nullable: true)]
        private ?int $maximumAmountCents = null,
        #[ORM\Column(length: 1024, nullable: true)]
        private ?string $encryptedUsername = null,
        #[ORM\Column(length: 1024, nullable: true)]
        private ?string $encryptedPassword = null,
        #[ORM\Column(length: 1024, nullable: true)]
        private ?string $encryptedApiKey = null,
        #[ORM\Column(length: 1024, nullable: true)]
        private ?string $encryptedSecretKey = null,
        #[ORM\Column(length: 1024, nullable: true)]
        private ?string $encryptedWebhookSecret = null,
        #[ORM\Column]
        private bool $isSandbox = false,
        #[ORM\Column(type: 'json')]
        private array $gatewayConfig = [],
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

    public function getPaymentMethod(): PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
        $this->touch();
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->touch();
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): void
    {
        $this->displayOrder = $displayOrder;
        $this->touch();
    }

    public function getMinimumAmountCents(): ?int
    {
        return $this->minimumAmountCents;
    }

    public function setMinimumAmountCents(?int $minimumAmountCents): void
    {
        $this->minimumAmountCents = $minimumAmountCents;
        $this->touch();
    }

    public function getMaximumAmountCents(): ?int
    {
        return $this->maximumAmountCents;
    }

    public function setMaximumAmountCents(?int $maximumAmountCents): void
    {
        $this->maximumAmountCents = $maximumAmountCents;
        $this->touch();
    }

    public function setEncryptedCredentials(
        ?string $encryptedUsername,
        ?string $encryptedPassword,
        ?string $encryptedApiKey,
        ?string $encryptedSecretKey,
        ?string $encryptedWebhookSecret,
    ): void {
        $this->encryptedUsername = $encryptedUsername;
        $this->encryptedPassword = $encryptedPassword;
        $this->encryptedApiKey = $encryptedApiKey;
        $this->encryptedSecretKey = $encryptedSecretKey;
        $this->encryptedWebhookSecret = $encryptedWebhookSecret;
        $this->touch();
    }

    public function getEncryptedUsername(): ?string
    {
        return $this->encryptedUsername;
    }

    public function getEncryptedPassword(): ?string
    {
        return $this->encryptedPassword;
    }

    public function getEncryptedApiKey(): ?string
    {
        return $this->encryptedApiKey;
    }

    public function getEncryptedSecretKey(): ?string
    {
        return $this->encryptedSecretKey;
    }

    public function getEncryptedWebhookSecret(): ?string
    {
        return $this->encryptedWebhookSecret;
    }

    public function isSandbox(): bool
    {
        return $this->isSandbox;
    }

    public function setIsSandbox(bool $isSandbox): void
    {
        $this->isSandbox = $isSandbox;
        $this->touch();
    }

    /** @return array<string, mixed> */
    public function getGatewayConfig(): array
    {
        return $this->gatewayConfig;
    }

    /** @param array<string, mixed> $gatewayConfig */
    public function setGatewayConfig(array $gatewayConfig): void
    {
        $this->gatewayConfig = $gatewayConfig;
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
