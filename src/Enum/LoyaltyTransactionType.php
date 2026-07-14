<?php

namespace App\Enum;

enum LoyaltyTransactionType: string
{
    case Earn = 'earn';
    case Redeem = 'redeem';
    case Expiration = 'expiration';
    case Cancellation = 'cancellation';
    case Correction = 'correction';
}
