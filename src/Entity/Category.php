<?php

namespace App\Entity;

use App\Doctrine\Traits\SoftDeleteTrait;
use App\Entity\Contract\SoftDeletableInterface;
use App\Enum\HomepageDisplayType;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'category')]
#[ORM\UniqueConstraint(name: 'uniq_category_boutique_slug', columns: ['boutique_id', 'slug'])]
class Category extends AbstractEntity implements SoftDeletableInterface
{
    use SoftDeleteTrait;

    /** @var Collection<int, Product> */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Product::class)]
    private Collection $products;

    /** @var Collection<int, ProductCategory> */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: ProductCategory::class)]
    private Collection $productCategories;

    /** @var Collection<int, Category> */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $children;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class, inversedBy: 'categories')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\Column(length: 160)]
        private string $name,
        #[ORM\Column(length: 180)]
        private string $slug,
        #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?self $parent = null,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $description = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $image = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $banner = null,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private bool $isFeatured = false,
        #[ORM\Column]
        private bool $showInHeader = false,
        #[ORM\Column]
        private bool $showOnHomepage = false,
        #[ORM\Column(length: 32, nullable: true, enumType: HomepageDisplayType::class)]
        private ?HomepageDisplayType $homepageDisplayType = null,
        #[ORM\Column]
        private int $homepagePosition = 0,
        #[ORM\Column]
        private int $menuPosition = 0,
        #[ORM\Column]
        private bool $showCategoryPage = true,
        #[ORM\Column]
        private int $productsLimit = 0,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $metaTitle = null,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $metaDescription = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $metaKeywords = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $ogTitle = null,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $ogDescription = null,
        #[ORM\Column(length: 500, nullable: true)]
        private ?string $ogImage = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
        $this->products = new ArrayCollection();
        $this->productCategories = new ArrayCollection();
        $this->children = new ArrayCollection();
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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
        $this->touch();
    }

    public function getBanner(): ?string
    {
        return $this->banner;
    }

    public function setBanner(?string $banner): void
    {
        $this->banner = $banner;
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

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): void
    {
        $this->isFeatured = $isFeatured;
        $this->touch();
    }

    public function getShowInHeader(): bool
    {
        return $this->showInHeader;
    }

    public function setShowInHeader(bool $showInHeader): void
    {
        $this->showInHeader = $showInHeader;
        $this->touch();
    }

    public function getShowOnHomepage(): bool
    {
        return $this->showOnHomepage;
    }

    public function setShowOnHomepage(bool $showOnHomepage): void
    {
        $this->showOnHomepage = $showOnHomepage;
        $this->touch();
    }

    public function getHomepageDisplayType(): ?HomepageDisplayType
    {
        return $this->homepageDisplayType;
    }

    public function setHomepageDisplayType(?HomepageDisplayType $type): void
    {
        $this->homepageDisplayType = $type;
        $this->touch();
    }

    public function getHomepagePosition(): int
    {
        return $this->homepagePosition;
    }

    public function setHomepagePosition(int $position): void
    {
        $this->homepagePosition = $position;
        $this->touch();
    }

    public function getMenuPosition(): int
    {
        return $this->menuPosition;
    }

    public function setMenuPosition(int $position): void
    {
        $this->menuPosition = $position;
        $this->touch();
    }

    public function getShowCategoryPage(): bool
    {
        return $this->showCategoryPage;
    }

    public function setShowCategoryPage(bool $show): void
    {
        $this->showCategoryPage = $show;
        $this->touch();
    }

    public function getProductsLimit(): int
    {
        return $this->productsLimit;
    }

    public function setProductsLimit(int $limit): void
    {
        $this->productsLimit = $limit;
        $this->touch();
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $title): void
    {
        $this->metaTitle = $title;
        $this->touch();
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $description): void
    {
        $this->metaDescription = $description;
        $this->touch();
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $keywords): void
    {
        $this->metaKeywords = $keywords;
        $this->touch();
    }

    public function getOgTitle(): ?string
    {
        return $this->ogTitle;
    }

    public function setOgTitle(?string $title): void
    {
        $this->ogTitle = $title;
        $this->touch();
    }

    public function getOgDescription(): ?string
    {
        return $this->ogDescription;
    }

    public function setOgDescription(?string $description): void
    {
        $this->ogDescription = $description;
        $this->touch();
    }

    public function getOgImage(): ?string
    {
        return $this->ogImage;
    }

    public function setOgImage(?string $image): void
    {
        $this->ogImage = $image;
        $this->touch();
    }

    public function getProductsCount(): int
    {
        return $this->products->count();
    }

    /** @return Collection<int, Category> */
    public function getChildren(): Collection
    {
        return $this->children;
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
