<?php

namespace App\Entity;

use App\Repository\ProductViewDailyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductViewDailyRepository::class)]
#[ORM\Table(name: 'product_view_daily')]
#[ORM\UniqueConstraint(name: 'uniq_product_view_daily', columns: ['product_id', 'view_date'])]
#[ORM\Index(name: 'idx_product_view_daily_date', columns: ['view_date'])]
class ProductViewDaily extends AbstractEntity
{
    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $viewDate;

    #[ORM\Column]
    private int $viewsCount = 0;

    public function __construct(Product $product, \DateTimeImmutable $viewDate)
    {
        parent::__construct();
        $this->product = $product;
        $this->viewDate = $viewDate->setTime(0, 0);
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getViewDate(): \DateTimeImmutable
    {
        return $this->viewDate;
    }

    public function getViewsCount(): int
    {
        return $this->viewsCount;
    }
}
