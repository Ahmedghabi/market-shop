<?php

namespace App\Enum;

enum LoyaltyTransactionType: string
{
    case Earn = 'earn';
    case Redeem = 'redeem';
    case Adjustment = 'adjustment';
    case Expiration = 'expiration';
}
