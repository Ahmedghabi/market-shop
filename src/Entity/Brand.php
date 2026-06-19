<?php

namespace App\Entity;

use App\Doctrine\Traits\SoftDeleteTrait;
use App\Entity\Contract\SoftDeletableInterface;
use App\Repository\BrandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BrandRepository::class)]
#[ORM\Table(name: 'brand')]
#[ORM\UniqueConstraint(name: 'uniq_brand_boutique_slug', columns: ['boutique_id', 'slug'])]
class Brand extends AbstractEntity implements SoftDeletableInterface
{
    use SoftDeleteTrait;

    /** @var Collection<int, Product> */
    #[ORM\OneToMany(mappedBy: 'brand', targetEntity: Product::class)]
    private Collection $products;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\Column(length: 160)]
        private string $name,
        #[ORM\Column(length: 180)]
        private string $slug,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $logo = null,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $description = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $website = null,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
        $this->products = new ArrayCollection();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
        $this->touch();
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): void
    {
        $this->logo = $logo;
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

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
        $this->touch();
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $active): void
    {
        $this->isActive = $active;
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

    public function getProductsCount(): int
    {
        return $this->products->count();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
