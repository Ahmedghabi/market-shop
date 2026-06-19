<?php

namespace App\Entity;

use App\Repository\ProductMediaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductMediaRepository::class)]
#[ORM\Table(name: 'product_media')]
class ProductMedia extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'media')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Product $product,
        #[ORM\Column(length: 32)]
        private string $type = 'IMAGE',
        #[ORM\Column(length: 255)]
        private string $filePath,
        #[ORM\Column]
        private int $position = 0,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $altText = null,
        #[ORM\Column]
        private bool $isPrimary = false,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $smallUrl = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $largeUrl = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        parent::__construct();
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $path): void
    {
        $this->filePath = $path;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $alt): void
    {
        $this->altText = $alt;
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function setIsPrimary(bool $primary): void
    {
        $this->isPrimary = $primary;
    }

    public function getSmallUrl(): ?string
    {
        return $this->smallUrl;
    }

    public function setSmallUrl(?string $url): void
    {
        $this->smallUrl = $url;
    }

    public function getLargeUrl(): ?string
    {
        return $this->largeUrl;
    }

    public function setLargeUrl(?string $url): void
    {
        $this->largeUrl = $url;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
