<?php

namespace App\Dto\Loyalty;

final class LoyaltyAvailableRewardOutput
{
    public string $id;
    public string $name;
    public ?string $description;
    public string $typeCode;
    public string $costType;
    public int $costValue;
    public bool $eligible;
    public ?string $reasonIneligible;
}
