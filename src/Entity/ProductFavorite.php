<?php

namespace App\Entity;

use App\Repository\ProductFavoriteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductFavoriteRepository::class)]
#[ORM\Table(name: 'product_favorite')]
#[ORM\UniqueConstraint(name: 'uniq_product_favorite_user', columns: ['user_id', 'boutique_id', 'product_id'])]
#[ORM\UniqueConstraint(name: 'uniq_product_favorite_session', columns: ['session_id', 'boutique_id', 'product_id'])]
#[ORM\Index(name: 'idx_product_favorite_user', columns: ['user_id'])]
#[ORM\Index(name: 'idx_product_favorite_session', columns: ['session_id'])]
#[ORM\Index(name: 'idx_product_favorite_boutique', columns: ['boutique_id'])]
#[ORM\Index(name: 'idx_product_favorite_product', columns: ['product_id'])]
class ProductFavorite extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
        private ?User $user,
        #[ORM\Column(length: 120, nullable: true)]
        private ?string $sessionId,
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\ManyToOne(targetEntity: Product::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Product $product,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
        $this->touch();
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): void
    {
        $this->sessionId = $sessionId;
        $this->touch();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getProduct(): Product
    {
        return $this->product;
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
