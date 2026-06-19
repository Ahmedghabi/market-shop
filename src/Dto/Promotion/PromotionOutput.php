<?php

namespace App\Dto\Promotion;

final class PromotionOutput
{
    public string $id;
    public string $boutiqueId;
    public string $name;
    public ?string $description;
    public string $scope;
    public string $type;
    public int $value;
    public int $priority;
    /** @var list<string> */
    public array $categoryIds = [];
    /** @var list<string> */
    public array $productIds = [];
    public ?string $startsAt;
    public ?string $endsAt;
    public bool $active;
    public bool $currentlyActive;
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
