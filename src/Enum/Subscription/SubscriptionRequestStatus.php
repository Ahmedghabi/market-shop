<?php

namespace App\Enum\Subscription;

enum SubscriptionRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
