<?php

namespace App\Enum;

enum RefundStatus: string
{
    case Pending = 'PENDING';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
    case Processed = 'PROCESSED';
    case Failed = 'FAILED';
}
