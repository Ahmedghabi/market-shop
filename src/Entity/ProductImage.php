<?php

namespace App\Entity;

use App\Doctrine\Traits\SoftDeleteTrait;
use App\Entity\Contract\SoftDeletableInterface;
use App\Repository\ProductImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductImageRepository::class)]
#[ORM\Table(name: 'product_image')]
class ProductImage extends AbstractEntity implements SoftDeletableInterface
{
    use SoftDeleteTrait;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'images')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Product $product,
        #[ORM\Column(length: 255)]
        private string $url,
        #[ORM\Column]
        private int $position = 0,
        #[ORM\Column(length: 160, nullable: true)]
        private ?string $alt = null,
    ) {
        parent::__construct();
    }

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $smallUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $largeUrl = null;

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getSmallUrl(): ?string
    {
        return $this->smallUrl;
    }

    public function setSmallUrl(?string $smallUrl): void
    {
        $this->smallUrl = $smallUrl;
    }

    public function getLargeUrl(): ?string
    {
        return $this->largeUrl;
    }

    public function setLargeUrl(?string $largeUrl): void
    {
        $this->largeUrl = $largeUrl;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }
}
