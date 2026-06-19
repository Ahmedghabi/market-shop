<?php

namespace App\Enum;

enum CheckoutMode: string
{
    case AccountOnly = 'ACCOUNT_ONLY';
    case GuestOnly = 'GUEST_ONLY';
    case GuestOrAccount = 'GUEST_OR_ACCOUNT';
}
