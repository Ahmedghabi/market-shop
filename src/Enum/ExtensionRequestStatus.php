<?php

namespace App\Enum;

enum ExtensionRequestStatus: string
{
    case Draft = 'draft';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case AwaitingValidation = 'awaiting_validation';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';
    case Activated = 'activated';
    case Expired = 'expired';
}
