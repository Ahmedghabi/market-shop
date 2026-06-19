<?php

namespace App\Enum;

enum CouponType: string
{
    case Percent = 'PERCENT';
    case FixedAmount = 'FIXED_AMOUNT';
    case FreeShipping = 'FREE_SHIPPING';
    case BuyXGetY = 'BUY_X_GET_Y';
}
