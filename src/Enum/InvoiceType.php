<?php

namespace App\Enum;

enum InvoiceType: string
{
    case Order = 'ORDER';
    case Subscription = 'SUBSCRIPTION';
    case Manual = 'MANUAL';
    case Refund = 'REFUND';
    case CreditNote = 'CREDIT_NOTE';
}
