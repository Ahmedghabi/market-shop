<?php

namespace App\Entity;

use App\Enum\ExtensionType;
use App\Repository\ExtensionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExtensionRepository::class)]
#[ORM\Table(name: 'extension')]
class Extension extends AbstractEntity
{
    public function __construct(
        #[ORM\Column(length: 80, unique: true)]
        private string $code,
        #[ORM\Column(length: 160)]
        private string $name,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $description = null,
        #[ORM\Column(length: 32, enumType: ExtensionType::class)]
        private ExtensionType $type = ExtensionType::Service,
        /**
         * Meaning depends on type: quota code for QuotaBoost, module code for Module,
         * theme code for Theme, arbitrary identifier for Service.
         */
        #[ORM\Column(length: 80, nullable: true)]
        private ?string $targetCode = null,
        /**
         * Numeric amount added on top of the plan's quota (only relevant for QuotaBoost).
         */
        #[ORM\Column(nullable: true)]
        private ?int $value = null,
        #[ORM\Column]
        private int $priceTnd = 0,
        /**
         * Null means permanent (no expiration) once activated.
         */
        #[ORM\Column(nullable: true)]
        private ?int $durationMonths = null,
        #[ORM\Column]
        private bool $requiresValidation = true,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column(length: 500, nullable: true)]
        private ?string $icon = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
        $this->touch();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->touch();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
        $this->touch();
    }

    public function getType(): ExtensionType
    {
        return $this->type;
    }

    public function setType(ExtensionType $type): void
    {
        $this->type = $type;
        $this->touch();
    }

    public function getTargetCode(): ?string
    {
        return $this->targetCode;
    }

    public function setTargetCode(?string $targetCode): void
    {
        $this->targetCode = $targetCode;
        $this->touch();
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(?int $value): void
    {
        $this->value = $value;
        $this->touch();
    }

    public function getPriceTnd(): int
    {
        return $this->priceTnd;
    }

    public function setPriceTnd(int $priceTnd): void
    {
        $this->priceTnd = $priceTnd;
        $this->touch();
    }

    public function getDurationMonths(): ?int
    {
        return $this->durationMonths;
    }

    public function setDurationMonths(?int $durationMonths): void
    {
        $this->durationMonths = $durationMonths;
        $this->touch();
    }

    public function requiresValidation(): bool
    {
        return $this->requiresValidation;
    }

    public function setRequiresValidation(bool $requiresValidation): void
    {
        $this->requiresValidation = $requiresValidation;
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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
        $this->touch();
    }

    public function isFree(): bool
    {
        return 0 === $this->priceTnd;
    }

    public function isPermanent(): bool
    {
        return null === $this->durationMonths;
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
