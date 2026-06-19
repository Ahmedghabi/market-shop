<?php

namespace App\Enum;

enum InvoiceStatus: string
{
    case Draft = 'DRAFT';
    case Pending = 'PENDING';
    case Paid = 'PAID';
    case PartiallyPaid = 'PARTIALLY_PAID';
    case Overdue = 'OVERDUE';
    case Cancelled = 'CANCELLED';
    case Refunded = 'REFUNDED';
}
