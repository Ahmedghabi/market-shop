<?php

namespace App\Enum;

enum PromotionType: string
{
    case Percentage = 'percentage';
    case FixedAmount = 'fixed_amount';
}
