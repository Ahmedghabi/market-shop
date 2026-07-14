<?php

namespace App\Entity;

use App\Repository\BoutiqueExtensionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BoutiqueExtensionRepository::class)]
#[ORM\Table(name: 'boutique_extension')]
class BoutiqueExtension extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\ManyToOne(targetEntity: Extension::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Extension $extension,
        #[ORM\Column]
        private \DateTimeImmutable $activatedAt = new \DateTimeImmutable(),
        /**
         * Null means the grant never expires (permanent extension).
         */
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $expiresAt = null,
        #[ORM\Column(length: 180, nullable: true)]
        private ?string $activatedBy = null,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $expiryNotifiedAt = null,
    ) {
        parent::__construct();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getExtension(): Extension
    {
        return $this->extension;
    }

    public function getActivatedAt(): \DateTimeImmutable
    {
        return $this->activatedAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function getActivatedBy(): ?string
    {
        return $this->activatedBy;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isExpired(\DateTimeImmutable $now): bool
    {
        return null !== $this->expiresAt && $this->expiresAt <= $now;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function reactivate(?\DateTimeImmutable $newExpiresAt): void
    {
        $this->isActive = true;
        $this->expiresAt = $newExpiresAt;
        $this->expiryNotifiedAt = null;
    }

    public function getExpiryNotifiedAt(): ?\DateTimeImmutable
    {
        return $this->expiryNotifiedAt;
    }

    public function markExpiryNotified(): void
    {
        $this->expiryNotifiedAt = new \DateTimeImmutable();
    }
}
