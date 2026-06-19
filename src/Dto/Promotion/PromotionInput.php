<?php

namespace App\Dto\Promotion;

use Symfony\Component\Validator\Constraints as Assert;

final class PromotionInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $name;

    public ?string $description = null;

    #[Assert\Choice(['global', 'category', 'product'])]
    public string $scope = 'global';

    #[Assert\Choice(['percentage', 'fixed_amount', 'buy_x_get_y'])]
    public string $type = 'percentage';

    #[Assert\PositiveOrZero]
    public int $value = 0;

    #[Assert\PositiveOrZero]
    public int $priority = 0;

    /** @var list<string> */
    public array $categoryIds = [];

    /** @var list<string> */
    public array $productIds = [];

    public ?string $startsAt = null;

    public ?string $endsAt = null;

    public bool $active = true;
}
