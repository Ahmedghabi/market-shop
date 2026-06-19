<?php

namespace App\Enum;

enum BoutiqueStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
    case Archived = 'archived';
}
